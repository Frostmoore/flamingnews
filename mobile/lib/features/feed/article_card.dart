import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:share_plus/share_plus.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../core/models/article.dart';
import '../../shared/widgets/lean_badge.dart';

// ── Costanti orientamento ──────────────────────────────────────────────────
const _leanOrder = ['left', 'center', 'right', 'international', 'altro'];

const _leanColors = {
  'left':          Color(0xFF2563EB),
  'center':        Color(0xFF6B7280),
  'right':         Color(0xFFDC2626),
  'international': Color(0xFFD97706),
  'altro':         Color(0xFF7C3AED),
};

const _leanBorderColors = {
  'left':          Color(0xFF93C5FD),
  'center':        Color(0xFFD1D5DB),
  'right':         Color(0xFFFCA5A5),
  'international': Color(0xFFFCD34D),
  'altro':         Color(0xFFDDD6FE),
};

const _leanLabels = {
  'left':          'Sinistra',
  'center':        'Centro',
  'right':         'Destra',
  'international': 'Internazionale',
  'altro':         'Media neutri',
};

// ── ArticleCard ────────────────────────────────────────────────────────────
class ArticleCard extends StatelessWidget {
  final Article article;
  final VoidCallback onTap;
  final VoidCallback? onLike;

  const ArticleCard({
    super.key,
    required this.article,
    required this.onTap,
    this.onLike,
  });

  /// Raggruppa la coverage per orientamento (solo le altre testate).
  Map<String, List<Map<String, dynamic>>> get _byLean {
    final groups = {for (final l in _leanOrder) l: <Map<String, dynamic>>[]};
    for (final src in article.coverage) {
      final l = src.lean ?? 'altro';
      (groups[l] ??= []).add({
        'id':          src.id,
        'title':       src.title ?? '',
        'source_name': src.sourceName,
        'url':         src.url,
      });
    }
    return groups;
  }

  void _share() {
    SharePlus.instance.share(ShareParams(
      subject: article.title,
      text: '${article.title}\n${article.url}',
    ));
  }

  @override
  Widget build(BuildContext context) {
    final hasCoverage = article.coverage.isNotEmpty;

    return GestureDetector(
      onTap: onTap,
      child: Card(
        elevation: 0,
        color: Colors.white,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.zero,
          side: BorderSide(color: Colors.grey.shade200),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Immagine
            if (article.urlToImage != null)
              AspectRatio(
                aspectRatio: 16 / 9,
                child: CachedNetworkImage(
                  imageUrl: article.urlToImage!,
                  fit: BoxFit.cover,
                  errorWidget: (ctx, _, __) => Container(
                    color: Colors.grey.shade100,
                    child: const Icon(Icons.image_not_supported_outlined, color: Colors.grey),
                  ),
                ),
              ),

            Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Badge orientamento + nome testata + data
                  Row(
                    children: [
                      LeanBadge(lean: article.politicalLean),
                      const SizedBox(width: 6),
                      Expanded(
                        child: Text(
                          article.sourceName ?? '',
                          style: const TextStyle(fontSize: 11, color: Colors.grey),
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      if (article.publishedAt != null)
                        Text(
                          DateFormat('d MMM', 'it').format(article.publishedAt!),
                          style: const TextStyle(fontSize: 11, color: Colors.grey),
                        ),
                    ],
                  ),
                  const SizedBox(height: 6),

                  // Titolo
                  Text(
                    article.title,
                    style: const TextStyle(
                      fontSize: 15,
                      fontWeight: FontWeight.w700,
                      color: Color(0xFF1A1A1A),
                      height: 1.3,
                    ),
                    maxLines: 3,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 8),

                  // Footer: categoria + like + share
                  Row(
                    children: [
                      Text(
                        article.category.toUpperCase(),
                        style: const TextStyle(
                          fontSize: 10, color: Color(0xFFC41E3A),
                          fontWeight: FontWeight.w700, letterSpacing: 0.8,
                        ),
                      ),
                      const Spacer(),
                      GestureDetector(
                        onTap: onLike,
                        behavior: HitTestBehavior.opaque,
                        child: Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 2),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(
                                article.liked ? Icons.favorite : Icons.favorite_border,
                                size: 16,
                                color: article.liked ? const Color(0xFFC41E3A) : Colors.grey.shade400,
                              ),
                              if (article.likesCount > 0) ...[
                                const SizedBox(width: 3),
                                Text(
                                  '${article.likesCount}',
                                  style: TextStyle(
                                    fontSize: 11,
                                    color: article.liked ? const Color(0xFFC41E3A) : Colors.grey.shade400,
                                  ),
                                ),
                              ],
                            ],
                          ),
                        ),
                      ),
                      const SizedBox(width: 8),
                      GestureDetector(
                        onTap: _share,
                        behavior: HitTestBehavior.opaque,
                        child: Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 2),
                          child: Icon(Icons.share_outlined, size: 16, color: Colors.grey.shade400),
                        ),
                      ),
                    ],
                  ),

                  // ── Sezione copertura mediale ──────────────────
                  const SizedBox(height: 10),
                  const Divider(height: 1, color: Color(0xFFEEEEEE)),
                  const SizedBox(height: 8),

                  if (hasCoverage)
                    _CoverageSection(byLean: _byLean)
                  else
                    _SingleSourceSection(
                      lean: article.politicalLean,
                      sourceName: article.sourceName,
                    ),
                ],
              ),
            ),

            // ── Barra orientamenti (bordo inferiore) ────────────
            _CoverageBar(
              coverage: article.coverage,
              selfLean: article.politicalLean,
            ),
          ],
        ),
      ),
    );
  }
}

// ── Sezione multi-fonte ────────────────────────────────────────────────────
class _CoverageSection extends StatelessWidget {
  final Map<String, List<Map<String, dynamic>>> byLean;

  const _CoverageSection({required this.byLean});

  @override
  Widget build(BuildContext context) {
    final activeLeans = _leanOrder.where((l) => byLean[l]?.isNotEmpty ?? false).toList();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'QUESTE TESTATE NE HANNO PARLATO',
          style: TextStyle(fontSize: 9, fontWeight: FontWeight.w800, color: Colors.black38, letterSpacing: 0.8),
        ),
        const SizedBox(height: 10),
        ...activeLeans.map((lean) => _LeanGroup(lean: lean, sources: byLean[lean]!)),
      ],
    );
  }
}

// ── Gruppo per orientamento ────────────────────────────────────────────────
class _LeanGroup extends StatelessWidget {
  final String lean;
  final List<Map<String, dynamic>> sources;

  const _LeanGroup({required this.lean, required this.sources});

  @override
  Widget build(BuildContext context) {
    final color = _leanColors[lean] ?? const Color(0xFF9CA3AF);
    final borderColor = _leanBorderColors[lean] ?? const Color(0xFFD1D5DB);
    final label = _leanLabels[lean] ?? lean;
    final count = sources.length;

    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Header orientamento
          Row(
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                decoration: BoxDecoration(color: color, borderRadius: BorderRadius.circular(20)),
                child: Text(
                  label.toUpperCase(),
                  style: const TextStyle(color: Colors.white, fontSize: 9, fontWeight: FontWeight.w800, letterSpacing: 0.5),
                ),
              ),
              const SizedBox(width: 6),
              Text(
                '$count ${count == 1 ? 'testata' : 'testate'}',
                style: const TextStyle(fontSize: 10, color: Colors.black38),
              ),
            ],
          ),
          const SizedBox(height: 6),
          // Lista titoli
          ...sources.map((src) => _SourceRow(src: src, borderColor: borderColor)),
        ],
      ),
    );
  }
}

// ── Riga singola fonte con titolo ─────────────────────────────────────────
class _SourceRow extends StatelessWidget {
  final Map<String, dynamic> src;
  final Color borderColor;

  const _SourceRow({required this.src, required this.borderColor});

  @override
  Widget build(BuildContext context) {
    final url = src['url'] as String? ?? '';
    final title = src['title'] as String? ?? '';
    final sourceName = src['source_name'] as String? ?? '';

    final content = Padding(
      padding: const EdgeInsets.only(bottom: 6),
      child: Container(
        padding: const EdgeInsets.only(left: 8),
        decoration: BoxDecoration(
          border: Border(left: BorderSide(color: borderColor, width: 2)),
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              sourceName.toUpperCase(),
              style: const TextStyle(fontSize: 9, fontWeight: FontWeight.w800, color: Colors.black45, letterSpacing: 0.5),
            ),
            const SizedBox(height: 2),
            Text(
              title,
              style: const TextStyle(fontSize: 12, color: Color(0xFF1A1A1A), height: 1.3),
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ),
      ),
    );

    if (url.isEmpty) return content;
    return GestureDetector(
      onTap: () => launchUrl(Uri.parse(url), mode: LaunchMode.externalApplication),
      child: content,
    );
  }
}

// ── Sezione fonte singola ──────────────────────────────────────────────────
class _SingleSourceSection extends StatelessWidget {
  final String? lean;
  final String? sourceName;

  const _SingleSourceSection({required this.lean, required this.sourceName});

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        const Text(
          'Solo questa testata ha pubblicato la notizia',
          style: TextStyle(fontSize: 11, color: Colors.black38),
        ),
        const SizedBox(height: 4),
        Row(
          children: [
            LeanBadge(lean: lean),
            if (lean != null) const SizedBox(width: 6),
            Text(
              sourceName ?? '',
              style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: Color(0xFF374151)),
            ),
          ],
        ),
      ],
    );
  }
}

// ── Barra orientamenti ─────────────────────────────────────────────────────
class _CoverageBar extends StatelessWidget {
  final List<CoverageSource> coverage;
  final String? selfLean;

  const _CoverageBar({required this.coverage, this.selfLean});

  @override
  Widget build(BuildContext context) {
    // Conta le testate per orientamento (incluso l'articolo principale)
    final counts = <String, int>{};
    if (selfLean != null) counts[selfLean!] = 1;
    for (final src in coverage) {
      final l = src.lean ?? 'altro';
      counts[l] = (counts[l] ?? 0) + 1;
    }

    final segments = _leanOrder
        .where((l) => (counts[l] ?? 0) > 0)
        .toList();

    if (segments.isEmpty) return const SizedBox.shrink();

    return SizedBox(
      height: 12,
      child: Row(
        children: segments
            .map((l) => Flexible(
                  flex: counts[l]!,
                  child: Container(color: _leanColors[l]!),
                ))
            .toList(),
      ),
    );
  }
}
