import 'package:flutter/material.dart';

class GoogleSignInButton extends StatelessWidget {
  final String label;
  final bool loading;
  final VoidCallback? onPressed;

  const GoogleSignInButton({
    super.key,
    required this.label,
    required this.onPressed,
    this.loading = false,
  });

  @override
  Widget build(BuildContext context) {
    return OutlinedButton(
      onPressed: loading ? null : onPressed,
      style: OutlinedButton.styleFrom(
        backgroundColor: Colors.white,
        side: BorderSide(color: Colors.grey.shade300),
        padding: const EdgeInsets.symmetric(vertical: 12),
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.zero),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          // Logo Google SVG-like via Stack di Container colorati
          SizedBox(
            width: 20,
            height: 20,
            child: CustomPaint(painter: _GoogleLogoPainter()),
          ),
          const SizedBox(width: 10),
          Text(
            label,
            style: const TextStyle(
              fontSize: 14,
              fontWeight: FontWeight.w600,
              color: Color(0xFF1A1A1A),
            ),
          ),
        ],
      ),
    );
  }
}

class _GoogleLogoPainter extends CustomPainter {
  @override
  void paint(Canvas canvas, Size size) {
    final center = Offset(size.width / 2, size.height / 2);
    final radius = size.width / 2;

    // Sfondo bianco
    canvas.drawCircle(center, radius, Paint()..color = Colors.white);

    // Disegna le 4 sezioni del logo Google con archi
    final colors = [
      const Color(0xFF4285F4), // blu
      const Color(0xFF34A853), // verde
      const Color(0xFFFBBC05), // giallo
      const Color(0xFFEA4335), // rosso
    ];

    final paint = Paint()
      ..style = PaintingStyle.stroke
      ..strokeWidth = size.width * 0.28;

    final rect = Rect.fromCircle(center: center, radius: radius * 0.62);

    for (int i = 0; i < 4; i++) {
      paint.color = colors[i];
      canvas.drawArc(rect, (i * 90 - 90) * 3.14159 / 180, 80 * 3.14159 / 180, false, paint);
    }
  }

  @override
  bool shouldRepaint(_GoogleLogoPainter oldDelegate) => false;
}
