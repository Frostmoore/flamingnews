import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../core/api/api_client.dart';
import '../../core/providers/auth_provider.dart';
import '../../shared/widgets/auth_field.dart';
import '../../shared/widgets/google_sign_in_button.dart';

const _red = Color(0xFFC41E3A);

// ── Colori orientamento politico ──────────────────────────────────────────────
Color _leanColor(String? lean) {
  return switch (lean) {
    'left'          => const Color(0xFF1D4ED8),
    'center-left'   => const Color(0xFF60A5FA),
    'center'        => const Color(0xFF6B7280),
    'center-right'  => const Color(0xFFFB923C),
    'right'         => const Color(0xFFDC2626),
    'international' => const Color(0xFFD97706),
    _               => const Color(0xFF7C3AED),
  };
}

class RegisterScreen extends ConsumerStatefulWidget {
  const RegisterScreen({super.key});

  @override
  ConsumerState<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends ConsumerState<RegisterScreen> {
  // Step 1
  final _nameCtrl     = TextEditingController();
  final _usernameCtrl = TextEditingController();
  final _emailCtrl    = TextEditingController();
  final _passCtrl     = TextEditingController();
  final _pass2Ctrl    = TextEditingController();

  // Step 2
  List<Map<String, dynamic>> _sources = [];
  final List<String> _selectedSources = [];
  bool _loadingSources = false;

  int _step = 1;
  String? _localError;

  @override
  void initState() {
    super.initState();
    // Aggiorna la barra forza password a ogni carattere digitato
    _passCtrl.addListener(() => setState(() {}));
  }

  @override
  void dispose() {
    _nameCtrl.dispose();
    _usernameCtrl.dispose();
    _emailCtrl.dispose();
    _passCtrl.dispose();
    _pass2Ctrl.dispose();
    super.dispose();
  }

  // ── Password strength ───────────────────────────────────────────────────────

  static const _reqLabels = [
    ('8+ caratteri',       r'.{8,}'),
    ('Maiuscola',          r'[A-Z]'),
    ('Minuscola',          r'[a-z]'),
    ('Numero',             r'[0-9]'),
    ('Carattere speciale', r'[^A-Za-z0-9]'),
  ];

  List<(String, bool)> get _reqs => _reqLabels
      .map((r) => (r.$1, RegExp(r.$2).hasMatch(_passCtrl.text)))
      .toList();

  int get _score => _reqs.where((r) => r.$2).length;

  Color get _barColor => switch (_score) {
    1 => const Color(0xFFF87171),
    2 => const Color(0xFFFB923C),
    3 => const Color(0xFFFACC15),
    4 => const Color(0xFF86EFAC),
    5 => const Color(0xFF22C55E),
    _ => Colors.grey.shade200,
  };

  // ── Navigazione ─────────────────────────────────────────────────────────────

  Future<void> _nextStep() async {
    setState(() => _localError = null);
    final name     = _nameCtrl.text.trim();
    final username = _usernameCtrl.text.trim();
    final email    = _emailCtrl.text.trim();

    if (name.isEmpty || username.isEmpty || email.isEmpty) {
      setState(() => _localError = 'Compila tutti i campi.');
      return;
    }
    if (_score < 5) {
      final missing = _reqs.where((r) => !r.$2).map((r) => r.$1).join(', ');
      setState(() => _localError = 'Password non sicura. Mancano: $missing.');
      return;
    }
    if (_passCtrl.text != _pass2Ctrl.text) {
      setState(() => _localError = 'Le password non coincidono.');
      return;
    }

    setState(() { _step = 2; _localError = null; });
    await _loadSources();
  }

  Future<void> _loadSources() async {
    if (_sources.isNotEmpty) return;
    setState(() => _loadingSources = true);
    try {
      final dio = ref.read(dioProvider);
      final res = await dio.get('/sources');
      setState(() {
        _sources = (res.data as List).cast<Map<String, dynamic>>();
        // Pre-seleziona tutte le testate
        _selectedSources.addAll(_sources.map((s) => s['domain'] as String));
      });
    } catch (_) {
      setState(() => _localError = 'Impossibile caricare le testate. Riprova.');
    } finally {
      setState(() => _loadingSources = false);
    }
  }

  Future<void> _register() async {
    if (_selectedSources.isEmpty) {
      setState(() => _localError = 'Seleziona almeno una testata.');
      return;
    }
    final success = await ref.read(authProvider.notifier).register(
      name:             _nameCtrl.text.trim(),
      username:         _usernameCtrl.text.trim(),
      email:            _emailCtrl.text.trim(),
      password:         _passCtrl.text,
      preferredSources: _selectedSources,
    );
    if (success && mounted) context.go('/email-sent');
  }

  Future<void> _loginWithGoogle() async {
    final success = await ref.read(authProvider.notifier).loginWithGoogle();
    if (!mounted || !success) return;
    final user = ref.read(authProvider).user;
    context.go(user?.needsSources == true ? '/categories' : '/');
  }

  void _toggleSource(String domain) {
    setState(() {
      _selectedSources.contains(domain)
          ? _selectedSources.remove(domain)
          : _selectedSources.add(domain);
    });
  }

  // ── Build ────────────────────────────────────────────────────────────────────

  @override
  Widget build(BuildContext context) {
    final state     = ref.watch(authProvider);
    final errorText = _localError ?? state.error;

    return Scaffold(
      backgroundColor: const Color(0xFFF8F6F1),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.symmetric(horizontal: 24, vertical: 32),
          child: Column(
            children: [
              const Text('FlamingNews',
                style: TextStyle(fontSize: 28, fontWeight: FontWeight.w800, color: _red)),
              const SizedBox(height: 8),
              // Step indicator
              Row(mainAxisAlignment: MainAxisAlignment.center, children: [
                _StepDot(active: _step >= 1, label: '1'),
                Container(width: 32, height: 2,
                    color: _step >= 2 ? _red : Colors.grey.shade300),
                _StepDot(active: _step >= 2, label: '2'),
              ]),
              const SizedBox(height: 24),
              Container(
                padding: const EdgeInsets.all(24),
                color: Colors.white,
                child: AnimatedSwitcher(
                  duration: const Duration(milliseconds: 200),
                  child: _step == 1
                      ? _buildStep1(state, errorText)
                      : _buildStep2(state, errorText),
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  // ── Step 1 ───────────────────────────────────────────────────────────────────

  Widget _buildStep1(AuthState state, String? errorText) {
    final hasPassword = _passCtrl.text.isNotEmpty;

    return Column(
      key: const ValueKey(1),
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        const Text('Crea il tuo account',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700)),
        const SizedBox(height: 20),

        if (errorText != null) _ErrorBox(errorText),

        GoogleSignInButton(
            label: 'Registrati con Google',
            loading: state.loading,
            onPressed: _loginWithGoogle),
        const SizedBox(height: 16),
        _divider(),
        const SizedBox(height: 16),

        AuthField(controller: _nameCtrl, label: 'Nome completo'),
        const SizedBox(height: 12),
        AuthField(
          controller: _usernameCtrl,
          label: 'Username',
          hint: 'mario_rossi',
        ),
        const SizedBox(height: 4),
        const Text('Solo lettere, numeri, trattini e underscore.',
            style: TextStyle(fontSize: 11, color: Colors.black38)),
        const SizedBox(height: 12),
        AuthField(controller: _emailCtrl, label: 'Email',
            type: TextInputType.emailAddress),
        const SizedBox(height: 12),
        AuthField(controller: _passCtrl, label: 'Password',
            obscure: true, showToggle: true),

        // Barra forza password
        if (hasPassword) ...[
          const SizedBox(height: 8),
          Row(
            children: List.generate(5, (i) => Expanded(
              child: Container(
                margin: const EdgeInsets.only(right: 3),
                height: 4,
                decoration: BoxDecoration(
                  color: _score > i ? _barColor : Colors.grey.shade200,
                  borderRadius: BorderRadius.circular(2),
                ),
              ),
            )),
          ),
          const SizedBox(height: 6),
          Wrap(
            spacing: 12,
            runSpacing: 2,
            children: _reqs.map((r) => Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                Icon(r.$2 ? Icons.check_circle : Icons.circle_outlined,
                    size: 12,
                    color: r.$2 ? const Color(0xFF22C55E) : Colors.grey.shade400),
                const SizedBox(width: 3),
                Text(r.$1,
                    style: TextStyle(
                        fontSize: 11,
                        color: r.$2 ? const Color(0xFF16A34A) : Colors.grey.shade400)),
              ],
            )).toList(),
          ),
        ],

        const SizedBox(height: 12),
        AuthField(controller: _pass2Ctrl, label: 'Conferma password',
            obscure: true, showToggle: true),
        const SizedBox(height: 20),

        ElevatedButton(
          onPressed: state.loading ? null : _nextStep,
          style: _btnStyle(),
          child: const Text('Continua →',
              style: TextStyle(fontWeight: FontWeight.w700)),
        ),
        const SizedBox(height: 16),
        GestureDetector(
          onTap: () => context.go('/login'),
          child: const Text.rich(
            TextSpan(
              text: 'Hai già un account? ',
              style: TextStyle(fontSize: 13, color: Colors.black54),
              children: [TextSpan(
                  text: 'Accedi',
                  style: TextStyle(color: _red, fontWeight: FontWeight.w700))],
            ),
            textAlign: TextAlign.center,
          ),
        ),
      ],
    );
  }

  // ── Step 2 ───────────────────────────────────────────────────────────────────

  Widget _buildStep2(AuthState state, String? errorText) {
    return Column(
      key: const ValueKey(2),
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        const Text('Scegli le tue testate',
            style: TextStyle(fontSize: 18, fontWeight: FontWeight.w700)),
        const SizedBox(height: 4),
        const Text('Seleziona i giornali che vuoi seguire.',
            style: TextStyle(fontSize: 13, color: Colors.black54)),
        const SizedBox(height: 6),
        Row(children: [
          Icon(Icons.rss_feed, size: 14, color: _red),
          const SizedBox(width: 4),
          const Text('AGGIORNATE VIA FEED RSS',
              style: TextStyle(fontSize: 10, fontWeight: FontWeight.w700,
                  color: _red, letterSpacing: 0.8)),
        ]),
        const SizedBox(height: 16),

        if (errorText != null) _ErrorBox(errorText),

        if (_loadingSources)
          const Padding(
            padding: EdgeInsets.symmetric(vertical: 32),
            child: Center(child: CircularProgressIndicator(color: _red, strokeWidth: 2)),
          )
        else
          ConstrainedBox(
            constraints: const BoxConstraints(maxHeight: 320),
            child: ListView.separated(
              shrinkWrap: true,
              itemCount: _sources.length,
              separatorBuilder: (_, __) => const SizedBox(height: 6),
              itemBuilder: (_, i) {
                final s       = _sources[i];
                final domain  = s['domain'] as String;
                final name    = s['name']   as String;
                final lean    = s['political_lean'] as String?;
                final sel     = _selectedSources.contains(domain);
                return GestureDetector(
                  onTap: () => _toggleSource(domain),
                  child: AnimatedContainer(
                    duration: const Duration(milliseconds: 120),
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
                    decoration: BoxDecoration(
                      color: sel ? const Color(0xFFFEF2F2) : Colors.white,
                      border: Border.all(
                          color: sel ? _red : Colors.grey.shade200,
                          width: sel ? 2 : 1),
                      borderRadius: BorderRadius.circular(4),
                    ),
                    child: Row(children: [
                      Container(
                        width: 10, height: 10,
                        decoration: BoxDecoration(
                          color: _leanColor(lean),
                          shape: BoxShape.circle,
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(name,
                              style: const TextStyle(
                                  fontSize: 13, fontWeight: FontWeight.w700,
                                  color: Color(0xFF1A1A1A))),
                          Text(domain,
                              style: TextStyle(fontSize: 11, color: Colors.grey.shade500)),
                        ],
                      )),
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                        decoration: BoxDecoration(
                          color: const Color(0xFFFEF2F2),
                          border: Border.all(color: _red.withValues(alpha: 0.3)),
                          borderRadius: BorderRadius.circular(3),
                        ),
                        child: const Text('RSS',
                            style: TextStyle(fontSize: 9, fontWeight: FontWeight.w800,
                                color: _red, letterSpacing: 0.5)),
                      ),
                      const SizedBox(width: 8),
                      if (sel)
                        const Icon(Icons.check_circle, color: _red, size: 18)
                      else
                        Icon(Icons.circle_outlined, color: Colors.grey.shade300, size: 18),
                    ]),
                  ),
                );
              },
            ),
          ),

        const SizedBox(height: 10),
        Row(children: [
          Text('${_selectedSources.length} selezionate',
              style: const TextStyle(fontSize: 12, color: Colors.black45)),
          const Spacer(),
          GestureDetector(
            onTap: () => setState(() {
              _selectedSources
                ..clear()
                ..addAll(_sources.map((s) => s['domain'] as String));
            }),
            child: const Text('Tutte',
                style: TextStyle(fontSize: 12, color: _red, fontWeight: FontWeight.w600)),
          ),
          const SizedBox(width: 12),
          GestureDetector(
            onTap: () => setState(() => _selectedSources.clear()),
            child: Text('Nessuna',
                style: TextStyle(fontSize: 12, color: Colors.grey.shade500)),
          ),
        ]),

        const SizedBox(height: 16),
        ElevatedButton(
          onPressed: (state.loading || _selectedSources.isEmpty) ? null : _register,
          style: _btnStyle(),
          child: state.loading
              ? const SizedBox(width: 18, height: 18,
                  child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
              : const Text('Inizia a leggere',
                  style: TextStyle(fontWeight: FontWeight.w700)),
        ),
        TextButton(
          onPressed: () => setState(() { _step = 1; _localError = null; }),
          child: const Text('← Indietro',
              style: TextStyle(color: Colors.black45, fontSize: 13)),
        ),
      ],
    );
  }

  // ── Helpers ──────────────────────────────────────────────────────────────────

  Widget _divider() => Row(children: [
    Expanded(child: Divider(color: Colors.grey.shade300)),
    Padding(
      padding: const EdgeInsets.symmetric(horizontal: 12),
      child: Text('oppure', style: TextStyle(fontSize: 11, color: Colors.grey.shade500)),
    ),
    Expanded(child: Divider(color: Colors.grey.shade300)),
  ]);

  ButtonStyle _btnStyle() => ElevatedButton.styleFrom(
    backgroundColor: _red,
    foregroundColor: Colors.white,
    disabledBackgroundColor: Colors.grey.shade300,
    padding: const EdgeInsets.symmetric(vertical: 14),
    shape: const RoundedRectangleBorder(borderRadius: BorderRadius.zero),
  );
}

// ── Componenti locali ─────────────────────────────────────────────────────────

class _ErrorBox extends StatelessWidget {
  final String message;
  const _ErrorBox(this.message);

  @override
  Widget build(BuildContext context) => Container(
    margin: const EdgeInsets.only(bottom: 14),
    padding: const EdgeInsets.all(10),
    color: const Color(0xFFFEF2F2),
    child: Text(message,
        style: const TextStyle(color: Color(0xFFDC2626), fontSize: 13)),
  );
}

class _StepDot extends StatelessWidget {
  final bool active;
  final String label;
  const _StepDot({required this.active, required this.label});

  @override
  Widget build(BuildContext context) => Container(
    width: 28, height: 28,
    decoration: BoxDecoration(
      shape: BoxShape.circle,
      color: active ? const Color(0xFFC41E3A) : Colors.grey.shade300,
    ),
    child: Center(
      child: Text(label,
          style: TextStyle(
              fontSize: 13, fontWeight: FontWeight.w700,
              color: active ? Colors.white : Colors.grey.shade500)),
    ),
  );
}
