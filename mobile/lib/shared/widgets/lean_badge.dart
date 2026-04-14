import 'package:flutter/material.dart';

class LeanBadge extends StatelessWidget {
  final String? lean;

  const LeanBadge({super.key, this.lean});

  static const _config = {
    'left':          ('Sinistra',       Color(0xFF2563EB)),
    'right':         ('Destra',         Color(0xFFDC2626)),
    'center':        ('Centro',         Color(0xFF6B7280)),
    'international': ('Int\'l',         Color(0xFFD97706)),
    'altro':         ('Altro',          Color(0xFF7C3AED)),
  };

  @override
  Widget build(BuildContext context) {
    final cfg = _config[lean] ?? ('', const Color(0xFF6B7280));
    if (cfg.$1.isEmpty) return const SizedBox.shrink();

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
      decoration: BoxDecoration(
        color: cfg.$2,
        borderRadius: BorderRadius.circular(20),
      ),
      child: Text(
        cfg.$1.toUpperCase(),
        style: const TextStyle(
          color: Colors.white,
          fontSize: 10,
          fontWeight: FontWeight.w700,
          letterSpacing: 0.5,
        ),
      ),
    );
  }
}
