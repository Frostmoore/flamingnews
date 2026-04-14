import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';
import 'package:intl/intl.dart';
import 'package:share_plus/share_plus.dart';
import 'package:url_launcher/url_launcher.dart';
import '../../core/models/article.dart';
import '../../shared/widgets/lean_badge.dart';

class ArticleCard extends StatefulWidget {
  final Article article;
  final VoidCallback onTap;
  final VoidCallback? onLike;

  const ArticleCard({
    super.key,
    required this.article,
    required this.onTap,
    this.onLike,
  });

  @override
  State<ArticleCard> createState() => _ArticleCardState();
}

class _ArticleCardState extends State<ArticleCard> {
  bool _showLeanDetail = false;

  // Raggruppa coverage per orientamento (include anche la fonte principale)
  Map<String, List<Map<String, dynamic>>> get _byLean {
    final groups = <String, List<Map<String, dynamic>>>{
      'left': [], 'center': [], 'right': [], 'international': [],
    };
    // Fonte principale
    groups[widget.article.politicalLean ?? 'center']!.add({
      'id': -1,
      'title': widget.article.title,
      'source_name': widget.article.sourceName,
      'url': widget.article.url,
    });
    // Coverage
    for (final src in widget.article.coverage) {
      final l = src.lean ?? 'center';
      groups[l] ??= [];
      groups[l]!.add({
        'id':          src.id,
        'title':       src.title ?? '',
        'source_name': src.sourceName,
        'url':         src.url,
      });
    }
    return groups;
  }

  int get _total => widget.article.coverage.length + 1;

  double _pct(String lean) {
    final count = _byLean[lean]?.length ?? 0;
    return _total > 0 ? count / _total : 0;
  }

  static const _leanColors = {
    'left':          Color(0xFF3B82F6),
    'center':        Color(0xFF9CA3AF),
    'right':         Color(0xFFEF4444),
    'international': Color(0xFFF59E0B),
  };
  static const _leanBorder = {
    'left':          Color(0xFF93C5FD),
    'right':         Color(0xFFFCA5A5),
    'center':        Color(0xFFD1D5DB),
    'international': Color(0xFFFCD34D),
  };
  static const _leanLabels = {
    'left':          'Sinistra',
    'center':        'Centro',
    'right':         'Destra',
    'international': 'Internazionale',
  };

  @override
  Widget build(BuildContext context) {
    final hasCoverage = widget.article.coverage.isNotEmpty;

    return GestureDetector(
      onTap: widget.onTap,
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
            if (widget.article.urlToImage != null)
              AspectRatio(
                aspectRatio: 16 / 9,
                child: CachedNetworkImage(
                  imageUrl: widget.article.urlToImage!,
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
                  // Badge + fonte + data
                  Row(
                    children: [
                      LeanBadge(lean: widget.article.politicalLean),
                      const SizedBox(width: 6),
                      Expanded(
                        child: Text(
                          widget.article.sourceName ?? '',
                          style: const TextStyle(fontSize: 11, color: Colors.grey),
                          overflow: TextOverflow.ellipsis,
                        ),
                      ),
                      if (widget.article.publishedAt != null)
                        Text(
                          DateFormat('d MMM', 'it').format(widget.article.publishedAt!),
                          style: const TextStyle(fontSize: 11, color: Colors.grey),
                        ),
                    ],
                  ),
                  const SizedBox(height: 6),
                  // Titolo
                  Text(
                    widget.article.title,
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
                        widget.article.category.toUpperCase(),
                        style: const TextStyle(
                          fontSize: 10, color: Color(0xFFC41E3A),
                          fontWeight: FontWeight.w700, letterSpacing: 0.8,
                        ),
                      ),
                      const Spacer(),
                      GestureDetector(
                        onTap: widget.onLike,
                        behavior: HitTestBehavior.opaque,
                        child: Padding(
                          padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 2),
                          child: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              Icon(
                                widget.article.liked ? Icons.favorite : Icons.favorite_border,
                                size: 16,
                                color: widget.article.liked ? const Color(0xFFC41E3A) : Colors.grey.shade400,
                              ),
                              if (widget.article.likesCount > 0) ...[
                                const SizedBox(width: 3),
                                Text(
                                  '${widget.article.likesCount}',
                                  style: TextStyle(
                                    fontSize: 11,
                                    color: widget.article.liked ? const Color(0xFFC41E3A) : Colors.grey.shade400,
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

                  // ── Coverage dashboard ──────────────────────────
                  const SizedBox(height: 10),
                  const Divider(height: 1, color: Color(0xFFEEEEEE)),
                  const SizedBox(height: 8),

                  if (hasCoverage) ...[
                    // Header copertura + toggle
                    GestureDetector(
                      onTap: () => setState(() => _showLeanDetail = !_showLeanDetail),
                      behavior: HitTestBehavior.opaque,
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            children: [
                              Text(
                                'Copertura media ($_total fonti)',
                                style: const TextStyle(
                                  fontSize: 10, fontWeight: FontWeight.w700,
                                  color: Colors.black38, letterSpacing: 0.5,
                                ),
                              ),
                              const Spacer(),
                              Icon(
                                _showLeanDetail ? Icons.keyboard_arrow_up : Icons.keyboard_arrow_down,
                                size: 16, color: Colors.black38,
                              ),
                            ],
                          ),
                          const SizedBox(height: 6),
                          // Barra lean proporzionale
                          _LeanBar(byLean: _byLean, total: _total, pct: _pct),
                          const SizedBox(height: 6),
                          // Legenda
                          _LeanLegend(byLean: _byLean, leanColors: _leanColors, leanLabels: _leanLabels),
                        ],
                      ),
                    ),
                    const SizedBox(height: 8),
                    // Fonti in piccolo (chips)
                    Wrap(
                      spacing: 6,
                      runSpacing: 4,
                      children: widget.article.coverage
                          .map((src) => _SourceChip(src: src, borderColor: _leanBorder[src.lean] ?? const Color(0xFFD1D5DB), dotColor: _leanColors[src.lean] ?? const Color(0xFF9CA3AF)))
                          .toList(),
                    ),
                    // Dettaglio titoli espandibile
                    if (_showLeanDetail) ...[
                      const SizedBox(height: 10),
                      _LeanTitlesDetail(byLean: _byLean, leanColors: _leanColors, leanLabels: _leanLabels),
                    ],
                  ] else
                    const Text('Solo questa fonte',
                        style: TextStyle(fontSize: 11, color: Colors.black26)),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _share() {
    SharePlus.instance.share(ShareParams(
      subject: widget.article.title,
      text: '${widget.article.title}\n${widget.article.url}',
    ));
  }
}

// ── Barra lean ─────────────────────────────────────────────
class _LeanBar extends StatelessWidget {
  final Map<String, List<Map<String, dynamic>>> byLean;
  final int total;
  final double Function(String) pct;

  const _LeanBar({required this.byLean, required this.total, required this.pct});

  static const _order = ['left', 'center', 'international', 'right'];
  static const _colors = {
    'left': Color(0xFF3B82F6), 'center': Color(0xFF9CA3AF),
    'right': Color(0xFFEF4444), 'international': Color(0xFFF59E0B),
  };

  @override
  Widget build(BuildContext context) {
    return ClipRRect(
      borderRadius: BorderRadius.circular(4),
      child: SizedBox(
        height: 8,
        child: Row(
          children: _order
              .where((l) => (byLean[l]?.isNotEmpty ?? false))
              .map((l) => Expanded(
                    flex: (pct(l) * 100).round(),
                    child: Container(color: _colors[l]),
                  ))
              .toList(),
        ),
      ),
    );
  }
}

// ── Legenda lean ───────────────────────────────────────────
class _LeanLegend extends StatelessWidget {
  final Map<String, List<Map<String, dynamic>>> byLean;
  final Map<String, Color> leanColors;
  final Map<String, String> leanLabels;

  const _LeanLegend({required this.byLean, required this.leanColors, required this.leanLabels});

  @override
  Widget build(BuildContext context) {
    return Wrap(
      spacing: 10,
      runSpacing: 4,
      children: ['left', 'center', 'right', 'international']
          .where((l) => (byLean[l]?.isNotEmpty ?? false))
          .map((l) => Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  Container(
                    width: 8, height: 8,
                    decoration: BoxDecoration(
                      color: leanColors[l], shape: BoxShape.circle,
                    ),
                  ),
                  const SizedBox(width: 4),
                  Text(
                    '${leanLabels[l]} ${byLean[l]!.length}',
                    style: TextStyle(fontSize: 10, color: leanColors[l], fontWeight: FontWeight.w600),
                  ),
                ],
              ))
          .toList(),
    );
  }
}

// ── Chip fonte ─────────────────────────────────────────────
class _SourceChip extends StatelessWidget {
  final CoverageSource src;
  final Color borderColor;
  final Color dotColor;

  const _SourceChip({required this.src, required this.borderColor, required this.dotColor});

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: () => launchUrl(Uri.parse(src.url), mode: LaunchMode.externalApplication),
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
        decoration: BoxDecoration(
          border: Border.all(color: borderColor),
          borderRadius: BorderRadius.circular(20),
          color: Colors.white,
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(width: 6, height: 6,
              decoration: BoxDecoration(color: dotColor, shape: BoxShape.circle)),
            const SizedBox(width: 4),
            Text(src.sourceName ?? src.sourceDomain ?? '',
              style: const TextStyle(fontSize: 11, color: Color(0xFF374151))),
          ],
        ),
      ),
    );
  }
}

// ── Dettaglio titoli per orientamento ─────────────────────
class _LeanTitlesDetail extends StatelessWidget {
  final Map<String, List<Map<String, dynamic>>> byLean;
  final Map<String, Color> leanColors;
  final Map<String, String> leanLabels;

  const _LeanTitlesDetail({required this.byLean, required this.leanColors, required this.leanLabels});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.all(10),
      decoration: BoxDecoration(
        color: const Color(0xFFF8F6F1),
        borderRadius: BorderRadius.circular(6),
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: ['left', 'center', 'right', 'international']
            .where((l) => byLean[l]?.isNotEmpty ?? false)
            .expand((l) => [
                  Text(
                    (leanLabels[l] ?? l).toUpperCase(),
                    style: TextStyle(
                      fontSize: 9, fontWeight: FontWeight.w800,
                      color: leanColors[l], letterSpacing: 0.8,
                    ),
                  ),
                  const SizedBox(height: 4),
                  ...byLean[l]!.map((src) => Padding(
                        padding: const EdgeInsets.only(bottom: 6),
                        child: GestureDetector(
                          onTap: () => launchUrl(
                            Uri.parse(src['url'] as String),
                            mode: LaunchMode.externalApplication,
                          ),
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                src['title'] as String? ?? '',
                                style: const TextStyle(
                                  fontSize: 12, color: Color(0xFF1A1A1A),
                                  height: 1.3, fontWeight: FontWeight.w600,
                                ),
                                maxLines: 2,
                                overflow: TextOverflow.ellipsis,
                              ),
                              Text(
                                src['source_name'] as String? ?? '',
                                style: const TextStyle(fontSize: 10, color: Colors.grey),
                              ),
                            ],
                          ),
                        ),
                      )),
                  const SizedBox(height: 8),
                ])
            .toList(),
      ),
    );
  }
}
