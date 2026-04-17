import 'package:flutter/material.dart';
import 'package:go_router/go_router.dart';

class EmailSentScreen extends StatelessWidget {
  const EmailSentScreen({super.key});

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8F6F1),
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            children: [
              const Text(
                'FlamingNews',
                style: TextStyle(fontSize: 24, fontWeight: FontWeight.w800, color: Color(0xFFC41E3A)),
              ),
              const Spacer(),
              Container(
                width: 72,
                height: 72,
                decoration: const BoxDecoration(
                  color: Color(0xFFFEF2F2),
                  shape: BoxShape.circle,
                ),
                child: const Icon(Icons.mark_email_unread_outlined, size: 36, color: Color(0xFFC41E3A)),
              ),
              const SizedBox(height: 24),
              const Text(
                'Controlla la tua email',
                style: TextStyle(fontSize: 22, fontWeight: FontWeight.w800, color: Color(0xFF1A1A1A)),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 12),
              const Text(
                'Abbiamo inviato un link di attivazione al tuo indirizzo email.\nCliccalo per attivare il tuo account e iniziare a leggere.',
                style: TextStyle(fontSize: 14, color: Colors.black54, height: 1.5),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 20),
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(12),
                color: Colors.white,
                child: const Text(
                  'Non vedi l\'email? Controlla la cartella spam o posta indesiderata.',
                  style: TextStyle(fontSize: 12, color: Colors.black45),
                  textAlign: TextAlign.center,
                ),
              ),
              const Spacer(),
              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: () => context.go('/login'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFFC41E3A),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    shape: const RoundedRectangleBorder(borderRadius: BorderRadius.zero),
                  ),
                  child: const Text('Torna al login', style: TextStyle(fontWeight: FontWeight.w700, fontSize: 15)),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
