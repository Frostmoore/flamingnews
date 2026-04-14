import 'package:flutter/material.dart';
import '../../core/models/article.dart';
import '../../shared/widgets/lean_badge.dart';

class SourceCard extends StatelessWidget {
  final Article article;
  final String lean;

  const SourceCard({super.key, required this.article, required this.lean});

  static const _borderColors = {
    'left':          Color(0xFF2563EB),
    'right':         Color(0xFFDC2626),
    'center':        Color(0xFF6B7280),
    'international': Color(0xFFD97706),
  };

  static const _bgColors = {
    'left':          Color(0xFFEFF6FF),
    'right':         Color(0xFFFEF2F2),
    'center':        Color(0xFFF9FAFB),
    'international': Color(0xFFFFFBEB),
  };

  @override
  Widget build(BuildContext context) {
    final borderColor = _borderColors[lean] ?? Colors.grey;
    final bgColor = _bgColors[lean] ?? Colors.grey.shade50;

    return Container(
      width: 220,
      margin: const EdgeInsets.only(right: 8),
      decoration: BoxDecoration(
        color: bgColor,
        border: Border(left: BorderSide(color: borderColor, width: 3)),
      ),
      padding: const EdgeInsets.all(12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          LeanBadge(lean: lean),
          const SizedBox(height: 6),
          Text(
            article.sourceName ?? article.sourceDomain ?? 'Fonte',
            style: const TextStyle(fontSize: 11, fontWeight: FontWeight.w600, color: Colors.black54),
          ),
          const SizedBox(height: 4),
          Text(
            article.title,
            style: const TextStyle(fontSize: 13, fontWeight: FontWeight.w700, color: Color(0xFF1A1A1A), height: 1.3),
            maxLines: 4,
            overflow: TextOverflow.ellipsis,
          ),
          if (article.description != null) ...[
            const SizedBox(height: 4),
            Text(
              article.description!,
              style: const TextStyle(fontSize: 11, color: Colors.black45, height: 1.4),
              maxLines: 2,
              overflow: TextOverflow.ellipsis,
            ),
          ],
        ],
      ),
    );
  }
}
