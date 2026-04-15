import 'package:flutter/material.dart';

class LeanBadge extends StatelessWidget {
  final String? lean;

  const LeanBadge({super.key, this.lean});

  static const _config = {
    'left':          ('Sinistra',       Color(0xFF1D4ED8)),
    'center-left':   ('Centro-sin.',    Color(0xFF60A5FA)),
    'center':        ('Centro',         Color(0xFF6B7280)),
    'center-right':  ('Centro-des.',    Color(0xFFFB923C)),
    'right':         ('Destra',         Color(0xFFDC2626)),
    'international': ('Int\'l',         Color(0xFFD97706)),
    'altro':         ('Media neutri',   Color(0xFF7C3AED)),
  };

  @override
  Widget build(BuildContext context) {
    final cfg = _config[lean];
    if (cfg == null || cfg.$1.isEmpty) return const SizedBox.shrink();

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
