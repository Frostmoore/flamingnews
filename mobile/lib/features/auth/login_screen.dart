import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../core/providers/auth_provider.dart';
import '../../shared/widgets/auth_field.dart';
import '../../shared/widgets/google_sign_in_button.dart';

class LoginScreen extends ConsumerStatefulWidget {
  const LoginScreen({super.key});

  @override
  ConsumerState<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends ConsumerState<LoginScreen> {
  final _loginCtrl = TextEditingController();
  final _passCtrl  = TextEditingController();

  @override
  void dispose() {
    _loginCtrl.dispose();
    _passCtrl.dispose();
    super.dispose();
  }

  Future<void> _login() async {
    final success = await ref.read(authProvider.notifier).login(
      _loginCtrl.text.trim(),
      _passCtrl.text,
    );
    if (!mounted || !success) return;
    final user = ref.read(authProvider).user;
    context.go(user?.needsCategories == true ? '/categories' : '/');
  }

  Future<void> _loginWithGoogle() async {
    final success = await ref.read(authProvider.notifier).loginWithGoogle();
    if (!mounted || !success) return;
    final user = ref.read(authProvider).user;
    context.go(user?.needsCategories == true ? '/categories' : '/');
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(authProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFF8F6F1),
      body: Center(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 40),
          child: Column(
            children: [
              const Text(
                'FlamingNews',
                style: TextStyle(fontSize: 28, fontWeight: FontWeight.w800, color: Color(0xFFC41E3A)),
              ),
              const SizedBox(height: 32),
              Container(
                padding: const EdgeInsets.all(24),
                color: Colors.white,
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    const Text('Accedi', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700)),
                    const SizedBox(height: 20),

                    // Errore
                    if (state.error != null)
                      Container(
                        margin: const EdgeInsets.only(bottom: 14),
                        padding: const EdgeInsets.all(10),
                        color: const Color(0xFFFEF2F2),
                        child: Text(state.error!, style: const TextStyle(color: Color(0xFFDC2626), fontSize: 13)),
                      ),

                    // Google
                    GoogleSignInButton(
                      label: 'Accedi con Google',
                      loading: state.loading,
                      onPressed: _loginWithGoogle,
                    ),
                    const SizedBox(height: 16),

                    // Separatore
                    Row(children: [
                      Expanded(child: Divider(color: Colors.grey.shade300)),
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 12),
                        child: Text('oppure', style: TextStyle(fontSize: 11, color: Colors.grey.shade500)),
                      ),
                      Expanded(child: Divider(color: Colors.grey.shade300)),
                    ]),
                    const SizedBox(height: 16),

                    // Email / password
                    AuthField(controller: _loginCtrl, label: 'Email o Username', hint: 'tu@esempio.it oppure mario_rossi'),
                    const SizedBox(height: 12),
                    AuthField(controller: _passCtrl, label: 'Password', obscure: true, showToggle: true),
                    const SizedBox(height: 8),
                    Align(
                      alignment: Alignment.centerRight,
                      child: GestureDetector(
                        onTap: () => context.go('/forgot-password'),
                        child: const Text('Password dimenticata?',
                            style: TextStyle(fontSize: 12, color: Color(0xFFC41E3A), fontWeight: FontWeight.w600)),
                      ),
                    ),
                    const SizedBox(height: 12),

                    ElevatedButton(
                      onPressed: state.loading ? null : _login,
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFFC41E3A),
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.zero),
                      ),
                      child: state.loading
                          ? const SizedBox(width: 18, height: 18,
                              child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                          : const Text('Accedi', style: TextStyle(fontWeight: FontWeight.w700)),
                    ),

                    const SizedBox(height: 16),
                    GestureDetector(
                      onTap: () => context.go('/register'),
                      child: const Text.rich(
                        TextSpan(
                          text: 'Non hai un account? ',
                          style: TextStyle(fontSize: 13, color: Colors.black54),
                          children: [
                            TextSpan(text: 'Registrati',
                                style: TextStyle(color: Color(0xFFC41E3A), fontWeight: FontWeight.w700)),
                          ],
                        ),
                        textAlign: TextAlign.center,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

