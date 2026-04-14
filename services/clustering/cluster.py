"""
FlamingNews — Microservizio di clustering articoli
Rilevamento "stesso evento" tramite TF-IDF su titoli + Jaccard overlap

Miglioramenti per World News API:
  - Stemming italiano (SnowballStemmer) per gestire varianti morfologiche
  - Bigrams nel TF-IDF per catturare entità nominate composte
  - Soglie abbassate per coprire phrasing più vario tra fonti diverse
  - Numeri preservati (date, punteggi, cifre sono identificatori di evento)

Porta: 8765 (interna)
"""

from __future__ import annotations

import logging
import re
import string
from collections import Counter
from typing import Any

import nltk
import uvicorn
from fastapi import FastAPI
from pydantic import BaseModel
from sklearn.metrics.pairwise import cosine_similarity
from sklearn.feature_extraction.text import TfidfVectorizer
import numpy as np

# ---------------------------------------------------------------------------
# Setup
# ---------------------------------------------------------------------------

logging.basicConfig(level=logging.INFO)
logger = logging.getLogger("flamingnews.clustering")

for resource in ("corpora/stopwords",):
    try:
        nltk.data.find(resource)
    except LookupError:
        nltk.download(resource.split("/")[1], quiet=True)

from nltk.corpus import stopwords
from nltk.stem.snowball import SnowballStemmer

STOPWORDS_IT = set(stopwords.words("italian"))
STOPWORDS_EN = set(stopwords.words("english"))
STOPWORDS = STOPWORDS_IT | STOPWORDS_EN

# Stemmer italiano — riduce le parole alla radice morfologica
STEMMER = SnowballStemmer("italian")

app = FastAPI(title="FlamingNews Clustering Service", version="3.0.0")


# ---------------------------------------------------------------------------
# Modelli Pydantic
# ---------------------------------------------------------------------------


class ArticleInput(BaseModel):
    id: int
    title: str
    text: str = ""  # non usato per clustering, mantenuto per compatibilità


class ClusterRequest(BaseModel):
    articles: list[ArticleInput]


class ClusterResult(BaseModel):
    cluster_id: int
    title: str
    keywords: list[str]
    article_ids: list[int]


class ClusterResponse(BaseModel):
    clusters: list[ClusterResult]
    total_articles: int
    total_clusters: int


# ---------------------------------------------------------------------------
# Preprocessing
# ---------------------------------------------------------------------------

# Punteggiatura tranne il trattino (utile in nomi composti) e apostrofo
_PUNCT = string.punctuation.replace("-", "").replace("'", "")
_PUNCT_TABLE = str.maketrans("", "", _PUNCT)


def tokenize_title(title: str) -> set[str]:
    """
    Tokenizza il titolo:
    - Lowercase
    - Rimuove URL
    - Mantiene numeri (identificatori di evento: anni, punteggi, cifre)
    - Rimuove punteggiatura (tranne trattino)
    - Rimuove stopword
    - Applica stemming italiano
    """
    title = title.lower()
    title = re.sub(r"http\S+|www\S+", "", title)        # rimuove URL
    title = re.sub(r"[«»""''„‟‹›]", " ", title)        # rimuove virgolette tipografiche
    title = title.translate(_PUNCT_TABLE)
    tokens = title.split()
    stemmed = set()
    for t in tokens:
        t = t.strip("-'")
        if not t or t in STOPWORDS or len(t) < 2:
            continue
        # Numeri: preserva, non stemmizzare
        if re.match(r"^\d+$", t):
            stemmed.add(t)
        else:
            stemmed.add(STEMMER.stem(t))
    return stemmed


def jaccard(a: set[str], b: set[str]) -> float:
    if not a or not b:
        return 0.0
    return len(a & b) / len(a | b)


def preprocess_title(title: str) -> str:
    """Restituisce stringa di token stemmati per TF-IDF."""
    return " ".join(sorted(tokenize_title(title)))


# ---------------------------------------------------------------------------
# Clustering core
# ---------------------------------------------------------------------------

def cluster_articles(
    articles: list[ArticleInput],
    tfidf_threshold: float = 0.48,   # abbassato da 0.55 — più fonti = phrasing più vario
    jaccard_threshold: float = 0.18, # abbassato da 0.25 — stemming compenserà la varianza
    min_cluster_size: int = 2,
) -> list[ClusterResult]:
    """
    Due articoli rappresentano lo "stesso evento" se:
      - cosine similarity TF-IDF (bigrams) sui titoli stemmati >= tfidf_threshold
      OR
      - Jaccard overlap dei token stemmati >= jaccard_threshold

    Clustering greedy union-find: se A≈B e B≈C, tutti e tre nello stesso cluster.
    Restituisce solo cluster con >= min_cluster_size articoli.
    """
    if len(articles) < min_cluster_size:
        return []

    processed = [preprocess_title(a.title) for a in articles]
    token_sets = [tokenize_title(a.title) for a in articles]

    # Filtra titoli vuoti dopo preprocessing
    valid = [
        (i, a)
        for i, (a, p) in enumerate(zip(articles, processed))
        if p.strip()
    ]
    if len(valid) < min_cluster_size:
        return []

    valid_indices  = [i for i, _ in valid]
    valid_articles = [a for _, a in valid]
    valid_processed = [processed[i] for i in valid_indices]
    valid_tokens    = [token_sets[i] for i in valid_indices]
    n = len(valid_articles)

    # TF-IDF cosine similarity — bigrams per catturare entità composte
    try:
        vec = TfidfVectorizer(
            ngram_range=(1, 2),   # unigrams + bigrams
            min_df=1,
            sublinear_tf=True,    # log(tf) — attenua termini molto frequenti
        )
        matrix = vec.fit_transform(valid_processed)
        sim = cosine_similarity(matrix)
    except Exception as e:
        logger.warning(f"TF-IDF error: {e}")
        sim = np.zeros((n, n))

    # Jaccard sui token stemmati
    jac = np.zeros((n, n))
    for i in range(n):
        for j in range(i + 1, n):
            score = jaccard(valid_tokens[i], valid_tokens[j])
            jac[i, j] = score
            jac[j, i] = score

    # Match: almeno uno dei due criteri soddisfatto
    match = (sim >= tfidf_threshold) | (jac >= jaccard_threshold)
    np.fill_diagonal(match, False)

    # Greedy union-find
    labels = [-1] * n
    current_label = 0

    for i in range(n):
        if labels[i] != -1:
            continue
        group: set[int] = {i}
        queue = [i]
        while queue:
            node = queue.pop()
            for j in range(n):
                if labels[j] == -1 and j not in group and match[node, j]:
                    group.add(j)
                    queue.append(j)
        if len(group) >= min_cluster_size:
            for idx in group:
                labels[idx] = current_label
            current_label += 1
        # Articoli isolati restano a -1

    # Costruisce risultati
    groups: dict[int, list[ArticleInput]] = {}
    for idx, label in enumerate(labels):
        if label != -1:
            groups.setdefault(label, []).append(valid_articles[idx])

    results: list[ClusterResult] = []
    for cluster_id, members in groups.items():
        # Keyword: token originali (non stemmati) più comuni tra i titoli del cluster
        all_tokens: list[str] = []
        for m in members:
            # Riusa tokenize ma senza stemming per le keyword leggibili
            raw = re.sub(r"http\S+|www\S+|[«»""''„‟‹›]", " ", m.title.lower())
            raw = raw.translate(_PUNCT_TABLE)
            all_tokens.extend(
                t for t in raw.split()
                if t not in STOPWORDS and len(t) > 2
            )
        top_keywords = [w for w, _ in Counter(all_tokens).most_common(6)]

        # Titolo del cluster = membro con titolo più lungo
        cluster_title = max(members, key=lambda a: len(a.title)).title

        results.append(ClusterResult(
            cluster_id=cluster_id,
            title=cluster_title,
            keywords=top_keywords,
            article_ids=[a.id for a in members],
        ))

    logger.info(
        f"Clustering: {len(results)} cluster da {len(valid_articles)} articoli validi "
        f"(tfidf≥{tfidf_threshold}, jaccard≥{jaccard_threshold})"
    )
    return results


# ---------------------------------------------------------------------------
# Endpoint FastAPI
# ---------------------------------------------------------------------------


@app.get("/health")
def health() -> dict[str, str]:
    return {"status": "ok", "service": "flamingnews-clustering", "version": "3.0.0"}


@app.post("/cluster", response_model=ClusterResponse)
def cluster_endpoint(request: ClusterRequest) -> ClusterResponse:
    logger.info(f"Richiesta clustering: {len(request.articles)} articoli")
    clusters = cluster_articles(request.articles)
    return ClusterResponse(
        clusters=clusters,
        total_articles=len(request.articles),
        total_clusters=len(clusters),
    )


# ---------------------------------------------------------------------------
# Entrypoint
# ---------------------------------------------------------------------------

if __name__ == "__main__":
    uvicorn.run(
        "cluster:app",
        host="127.0.0.1",
        port=8765,
        reload=False,
        log_level="info",
    )
