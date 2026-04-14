# FlamingNews — Clustering Microservice

Microservizio FastAPI per il clustering tematico degli articoli.

## Algoritmo

- **TF-IDF** (Term Frequency - Inverse Document Frequency) su titolo + primo paragrafo
- **Agglomerative Clustering** con distanza coseno
- Soglia di similarità configurabile (default: 0.30)
- Estrazione automatica keyword per ogni cluster

## Setup

```bash
cd services/clustering

# Crea virtualenv (consigliato)
python -m venv venv
source venv/bin/activate  # Linux/Mac
venv\Scripts\activate     # Windows

# Installa dipendenze
pip install -r requirements.txt
```

## Avvio

```bash
uvicorn cluster:app --host 127.0.0.1 --port 8765
```

## Endpoint

### `GET /health`
Verifica che il servizio sia attivo.

```json
{"status": "ok", "service": "flamingnews-clustering"}
```

### `POST /cluster`
Riceve articoli e restituisce i cluster tematici.

**Request:**
```json
{
  "articles": [
    {"id": 1, "title": "Governo approva manovra", "text": "Il consiglio dei ministri..."},
    {"id": 2, "title": "La manovra economica del governo", "text": "Approvato il bilancio..."}
  ]
}
```

**Response:**
```json
{
  "clusters": [
    {
      "cluster_id": 0,
      "title": "Governo approva manovra",
      "keywords": ["manovra", "governo", "bilancio"],
      "article_ids": [1, 2]
    }
  ],
  "total_articles": 2,
  "total_clusters": 1
}
```
