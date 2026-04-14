import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../core/providers/auth_provider.dart';
import '../../shared/widgets/auth_field.dart';
import '../../shared/widgets/google_sign_in_button.dart';

const _allCategories = [
  ('politica',   'Politica',   '🏛️'),
  ('economia',   'Economia',   '📈'),
  ('esteri',     'Esteri',     '🌍'),
  ('tecnologia', 'Tecnologia', '💻'),
  ('sport',      'Sport',      '⚽'),
  ('cultura',    'Cultura',    '🎭'),
  ('generale',   'Generale',   '🗞️'),
  ('scienza',    'Scienza',    '🔬'),
  ('salute',     'Salute',     '🏥'),
];

class RegisterScreen extends ConsumerStatefulWidget {
  const RegisterScreen({super.key});

  @override
  ConsumerState<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends ConsumerState<RegisterScreen> {
  // Step 1 controllers
  final _nameCtrl  = TextEditingController();
  final _emailCtrl = TextEditingController();
  final _passCtrl  = TextEditingController();
  final _pass2Ctrl = TextEditingController();

  // Step 2 — categorie
  final List<String> _selectedCategories = [];

  int _step = 1;
  String? _localError;

  @override
  void dispose() {
    _nameCtrl.dispose();
    _emailCtrl.dispose();
    _passCtrl.dispose();
    _pass2Ctrl.dispose();
    super.dispose();
  }

  void _nextStep() {
    setState(() => _localError = null);
    if (_nameCtrl.text.trim().isEmpty || _emailCtrl.text.trim().isEmpty) {
      setState(() => _localError = 'Compila tutti i campi.');
      return;
    }
    if (_passCtrl.text.length < 8) {
      setState(() => _localError = 'La password deve essere di almeno 8 caratteri.');
      return;
    }
    if (_passCtrl.text != _pass2Ctrl.text) {
      setState(() => _localError = 'Le password non coincidono.');
      return;
    }
    setState(() => _step = 2);
  }

  Future<void> _register() async {
    if (_selectedCategories.isEmpty) {
      setState(() => _localError = 'Seleziona almeno un interesse.');
      return;
    }
    final success = await ref.read(authProvider.notifier).register(
      _nameCtrl.text.trim(),
      _emailCtrl.text.trim(),
      _passCtrl.text,
      _selectedCategories,
    );
    if (success && mounted) context.go('/');
  }

  Future<void> _loginWithGoogle() async {
    final success = await ref.read(authProvider.notifier).loginWithGoogle();
    if (!mounted || !success) return;
    final user = ref.read(authProvider).user;
    context.go(user?.needsCategories == true ? '/categories' : '/');
  }

  void _toggleCategory(String val) {
    setState(() {
      _selectedCategories.contains(val)
          ? _selectedCategories.remove(val)
          : _selectedCategories.add(val);
    });
  }

  @override
  Widget build(BuildContext context) {
    final state      = ref.watch(authProvider);
    final errorText  = _localError ?? state.error;

    return Scaffold(
      backgroundColor: const Color(0xFFF8F6F1),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 32),
          child: Column(
            children: [
              const Text(
                'FlamingNews',
                style: TextStyle(fontSize: 28, fontWeight: FontWeight.w800, color: Color(0xFFC41E3A)),
              ),
              const SizedBox(height: 8),
              // Indicatore step
              Row(mainAxisAlignment: MainAxisAlignment.center, children: [
                _StepDot(active: _step >= 1, label: '1'),
                Container(width: 32, height: 2, color: _step >= 2 ? const Color(0xFFC41E3A) : Colors.grey.shade300),
                _StepDot(active: _step >= 2, label: '2'),
              ]),
              const SizedBox(height: 24),

              Container(
                padding: const EdgeInsets.all(24),
                color: Colors.white,
                child: AnimatedSwitcher(
                  duration: const Duration(milliseconds: 200),
                  child: _step == 1 ? _buildStep1(state, errorText) : _buildStep2(state, errorText),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ---------------------------------------------------------------------------
  // Step 1 — dati account
  // ---------------------------------------------------------------------------

  Widget _buildStep1(AuthState state, String? errorText) {
    return Column(
      key: const ValueKey(1),
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        const Text('Crea il tuo account', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700)),
        const SizedBox(height: 20),

        if (errorText != null)
          Container(
            margin: const EdgeInsets.only(bottom: 14),
            padding: const EdgeInsets.all(10),
            color: const Color(0xFFFEF2F2),
            child: Text(errorText, style: const TextStyle(color: Color(0xFFDC2626), fontSize: 13)),
          ),

        GoogleSignInButton(
          label: 'Registrati con Google',
          loading: state.loading,
          onPressed: _loginWithGoogle,
        ),
        const SizedBox(height: 16),
        Row(children: [
          Expanded(child: Divider(color: Colors.grey.shade300)),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 12),
            child: Text('oppure', style: TextStyle(fontSize: 11, color: Colors.grey.shade500)),
          ),
          Expanded(child: Divider(color: Colors.grey.shade300)),
        ]),
        const SizedBox(height: 16),

        AuthField(controller: _nameCtrl, label: 'Nome completo'),
        const SizedBox(height: 12),
        AuthField(controller: _emailCtrl, label: 'Email', type: TextInputType.emailAddress),
        const SizedBox(height: 12),
        AuthField(controller: _passCtrl, label: 'Password (min. 8 caratteri)', obscure: true),
        const SizedBox(height: 12),
        AuthField(controller: _pass2Ctrl, label: 'Conferma password', obscure: true),
        const SizedBox(height: 20),

        ElevatedButton(
          onPressed: state.loading ? null : _nextStep,
          style: ElevatedButton.styleFrom(
            backgroundColor: const Color(0xFFC41E3A),
            foregroundColor: Colors.white,
            padding: const EdgeInsets.symmetric(vertical: 14),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.zero),
          ),
          child: const Text('Continua →', style: TextStyle(fontWeight: FontWeight.w700)),
        ),
        const SizedBox(height: 16),
        GestureDetector(
          onTap: () => context.go('/login'),
          child: const Text.rich(
            TextSpan(
              text: 'Hai già un account? ',
              style: TextStyle(fontSize: 13, color: Colors.black54),
              children: [TextSpan(text: 'Accedi', style: TextStyle(color: Color(0xFFC41E3A), fontWeight: FontWeight.w700))],
            ),
            textAlign: TextAlign.center,
          ),
        ),
      ],
    );
  }

  // ---------------------------------------------------------------------------
  // Step 2 — selezione categorie
  // ---------------------------------------------------------------------------

  Widget _buildStep2(AuthState state, String? errorText) {
    return Column(
      key: const ValueKey(2),
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        const Text('I tuoi interessi', style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700)),
        const SizedBox(height: 6),
        const Text(
          'Seleziona almeno un tema per personalizzare il tuo feed.',
          style: TextStyle(fontSize: 13, color: Colors.black54, height: 1.4),
        ),
        const SizedBox(height: 20),

        if (errorText != null)
          Container(
            margin: const EdgeInsets.only(bottom: 14),
            padding: const EdgeInsets.all(10),
            color: const Color(0xFFFEF2F2),
            child: Text(errorText, style: const TextStyle(color: Color(0xFFDC2626), fontSize: 13)),
          ),

        GridView.count(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          crossAxisCount: 2,
          crossAxisSpacing: 8,
          mainAxisSpacing: 8,
          childAspectRatio: 1.5,
          children: _allCategories.map((cat) {
            final isSelected = _selectedCategories.contains(cat.$1);
            return GestureDetector(
              onTap: () => _toggleCategory(cat.$1),
              child: AnimatedContainer(
                duration: const Duration(milliseconds: 150),
                decoration: BoxDecoration(
                  color: isSelected ? const Color(0xFFFEF2F2) : const Color(0xFFFAFAFA),
                  border: Border.all(
                    color: isSelected ? const Color(0xFFC41E3A) : Colors.grey.shade200,
                    width: isSelected ? 2 : 1,
                  ),
                ),
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text(cat.$3, style: const TextStyle(fontSize: 22)),
                    const SizedBox(height: 4),
                    Text(
                      cat.$2,
                      style: TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w700,
                        color: isSelected ? const Color(0xFFC41E3A) : const Color(0xFF1A1A1A),
                      ),
                    ),
                    if (isSelected)
                      const Icon(Icons.check_circle, color: Color(0xFFC41E3A), size: 14),
                  ],
                ),
              ),
            );
          }).toList(),
        ),

        const SizedBox(height: 20),
        ElevatedButton(
          onPressed: (state.loading || _selectedCategories.isEmpty) ? null : _register,
          style: ElevatedButton.styleFrom(
            backgroundColor: const Color(0xFFC41E3A),
            foregroundColor: Colors.white,
            padding: const EdgeInsets.symmetric(vertical: 14),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.zero),
            disabledBackgroundColor: Colors.grey.shade300,
          ),
          child: state.loading
              ? const SizedBox(width: 18, height: 18,
                  child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
              : const Text('Inizia a leggere', style: TextStyle(fontWeight: FontWeight.w700)),
        ),
        TextButton(
          onPressed: () => setState(() { _step = 1; _localError = null; }),
          child: const Text('← Indietro', style: TextStyle(color: Colors.black45, fontSize: 13)),
        ),
      ],
    );
  }
}

class _StepDot extends StatelessWidget {
  final bool active;
  final String label;

  const _StepDot({required this.active, required this.label});

  @override
  Widget build(BuildContext context) {
    return Container(
      width: 28,
      height: 28,
      decoration: BoxDecoration(
        shape: BoxShape.circle,
        color: active ? const Color(0xFFC41E3A) : Colors.grey.shade300,
      ),
      child: Center(
        child: Text(
          label,
          style: TextStyle(
            fontSize: 13,
            fontWeight: FontWeight.w700,
            color: active ? Colors.white : Colors.grey.shade500,
          ),
        ),
      ),
    );
  }
}
