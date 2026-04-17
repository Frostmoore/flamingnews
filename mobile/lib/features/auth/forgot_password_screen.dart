import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../core/providers/auth_provider.dart';
import '../../shared/widgets/auth_field.dart';

class ForgotPasswordScreen extends ConsumerStatefulWidget {
  const ForgotPasswordScreen({super.key});

  @override
  ConsumerState<ForgotPasswordScreen> createState() => _ForgotPasswordScreenState();
}

class _ForgotPasswordScreenState extends ConsumerState<ForgotPasswordScreen> {
  final _emailCtrl = TextEditingController();
  bool _sent = false;

  @override
  void dispose() {
    _emailCtrl.dispose();
    super.dispose();
  }

  Future<void> _send() async {
    final ok = await ref.read(authProvider.notifier).forgotPassword(_emailCtrl.text.trim());
    if (ok && mounted) setState(() => _sent = true);
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(authProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFF8F6F1),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back, color: Color(0xFF1A1A1A)),
          onPressed: () => context.go('/login'),
        ),
        title: const Text('Password dimenticata',
            style: TextStyle(color: Color(0xFF1A1A1A), fontWeight: FontWeight.w700, fontSize: 16)),
      ),
      body: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 40),
          child: Column(
            children: [
              const Text('FlamingNews',
                  style: TextStyle(fontSize: 28, fontWeight: FontWeight.w800, color: Color(0xFFC41E3A))),
              const SizedBox(height: 32),
              Container(
                padding: const EdgeInsets.all(24),
                color: Colors.white,
                child: _sent ? _buildSent() : _buildForm(state),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildForm(AuthState state) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        const Text('Reimposta la password',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700)),
        const SizedBox(height: 8),
        const Text(
          'Inserisci la tua email e ti invieremo un link per impostare una nuova password.',
          style: TextStyle(fontSize: 13, color: Colors.black54, height: 1.5),
        ),
        const SizedBox(height: 20),

        if (state.error != null)
          Container(
            margin: const EdgeInsets.only(bottom: 14),
            padding: const EdgeInsets.all(10),
            color: const Color(0xFFFEF2F2),
            child: Text(state.error!,
                style: const TextStyle(color: Color(0xFFDC2626), fontSize: 13)),
          ),

        AuthField(
          controller: _emailCtrl,
          label: 'Email',
          type: TextInputType.emailAddress,
          hint: 'tu@esempio.it',
        ),
        const SizedBox(height: 20),

        ElevatedButton(
          onPressed: state.loading ? null : _send,
          style: ElevatedButton.styleFrom(
            backgroundColor: const Color(0xFFC41E3A),
            foregroundColor: Colors.white,
            padding: const EdgeInsets.symmetric(vertical: 14),
            shape: const RoundedRectangleBorder(borderRadius: BorderRadius.zero),
          ),
          child: state.loading
              ? const SizedBox(width: 18, height: 18,
                  child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
              : const Text('Invia link di ripristino',
                  style: TextStyle(fontWeight: FontWeight.w700)),
        ),

        const SizedBox(height: 16),
        GestureDetector(
          onTap: () => context.go('/login'),
          child: const Text('← Torna al login',
              textAlign: TextAlign.center,
              style: TextStyle(fontSize: 13, color: Color(0xFFC41E3A), fontWeight: FontWeight.w600)),
        ),
      ],
    );
  }

  Widget _buildSent() {
    return Column(
      children: [
        Container(
          width: 64, height: 64,
          decoration: const BoxDecoration(color: Color(0xFFFEF2F2), shape: BoxShape.circle),
          child: const Icon(Icons.mark_email_read_outlined, size: 32, color: Color(0xFFC41E3A)),
        ),
        const SizedBox(height: 20),
        const Text('Controlla la tua email',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.w800),
            textAlign: TextAlign.center),
        const SizedBox(height: 10),
        const Text(
          'Se l\'indirizzo è associato a un account, riceverai un\'email con il link per reimpostare la password.',
          style: TextStyle(fontSize: 13, color: Colors.black54, height: 1.5),
          textAlign: TextAlign.center,
        ),
        const SizedBox(height: 16),
        Container(
          width: double.infinity,
          padding: const EdgeInsets.all(12),
          color: const Color(0xFFF9FAFB),
          child: const Text(
            'Non vedi l\'email? Controlla la cartella spam.',
            style: TextStyle(fontSize: 12, color: Colors.black45),
            textAlign: TextAlign.center,
          ),
        ),
        const SizedBox(height: 20),
        SizedBox(
          width: double.infinity,
          child: ElevatedButton(
            onPressed: () => context.go('/login'),
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFFC41E3A),
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(vertical: 14),
              shape: const RoundedRectangleBorder(borderRadius: BorderRadius.zero),
            ),
            child: const Text('Torna al login',
                style: TextStyle(fontWeight: FontWeight.w700)),
          ),
        ),
      ],
    );
  }
}
