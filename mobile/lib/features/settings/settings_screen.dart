import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../core/providers/auth_provider.dart';
import '../../core/providers/user_feeds_provider.dart';
import '../../shared/widgets/auth_field.dart';
import 'user_feeds_screen.dart';

const _red = Color(0xFFC41E3A);

class SettingsScreen extends ConsumerStatefulWidget {
  const SettingsScreen({super.key});

  @override
  ConsumerState<SettingsScreen> createState() => _SettingsScreenState();
}

class _SettingsScreenState extends ConsumerState<SettingsScreen> {
  // controllers per i dialog
  final _nameCtrl     = TextEditingController();
  final _usernameCtrl = TextEditingController();
  final _emailCtrl    = TextEditingController();

  @override
  void dispose() {
    _nameCtrl.dispose();
    _usernameCtrl.dispose();
    _emailCtrl.dispose();
    super.dispose();
  }

  // ── Dialogo modifica dati personali ─────────────────────────────────────────

  Future<void> _showEditProfileDialog() async {
    final user = ref.read(authProvider).user!;
    _nameCtrl.text     = user.name;
    _usernameCtrl.text = user.username ?? '';
    _emailCtrl.text    = user.email;

    String? localError;
    bool emailWillChange = false;

    await showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setS) {
          return AlertDialog(
            backgroundColor: Colors.white,
            shape: const RoundedRectangleBorder(borderRadius: BorderRadius.zero),
            title: const Text('Modifica profilo',
                style: TextStyle(fontSize: 17, fontWeight: FontWeight.w800)),
            content: SingleChildScrollView(
              child: Column(
                mainAxisSize: MainAxisSize.min,
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  if (localError != null) ...[
                    Container(
                      padding: const EdgeInsets.all(10),
                      color: const Color(0xFFFEF2F2),
                      child: Text(localError!,
                          style: const TextStyle(color: Color(0xFFDC2626), fontSize: 12)),
                    ),
                    const SizedBox(height: 12),
                  ],
                  AuthField(controller: _nameCtrl, label: 'Nome'),
                  const SizedBox(height: 10),
                  AuthField(controller: _usernameCtrl, label: 'Username',
                      hint: 'mario_rossi'),
                  const SizedBox(height: 10),
                  AuthField(controller: _emailCtrl, label: 'Email',
                      type: TextInputType.emailAddress),
                  if (emailWillChange) ...[
                    const SizedBox(height: 6),
                    const Text(
                      'Cambiando l\'email dovrai verificarla di nuovo.',
                      style: TextStyle(fontSize: 11, color: Color(0xFFD97706)),
                    ),
                  ],
                ],
              ),
            ),
            actions: [
              TextButton(
                onPressed: () => Navigator.pop(ctx),
                child: const Text('Annulla', style: TextStyle(color: Colors.black45)),
              ),
              Consumer(
                builder: (_, ref2, __) {
                  final loading = ref2.watch(authProvider).loading;
                  return ElevatedButton(
                    style: ElevatedButton.styleFrom(
                      backgroundColor: _red,
                      foregroundColor: Colors.white,
                      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.zero),
                    ),
                    onPressed: loading ? null : () async {
                      setS(() {
                        localError = null;
                        emailWillChange = _emailCtrl.text.trim() != user.email;
                      });
                      final nav = Navigator.of(ctx);
                      final result = await ref.read(authProvider.notifier).updateProfile(
                        name:     _nameCtrl.text.trim(),
                        username: _usernameCtrl.text.trim(),
                        email:    _emailCtrl.text.trim(),
                      );
                      if (!mounted) return;
                      if (result['ok'] == true) {
                        nav.pop();
                        _showSnack(result['email_changed'] == true
                            ? 'Profilo salvato. Verifica la nuova email.'
                            : 'Profilo aggiornato.');
                      } else {
                        setS(() => localError = ref.read(authProvider).error);
                      }
                    },
                    child: loading
                        ? const SizedBox(width: 16, height: 16,
                            child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                        : const Text('Salva', style: TextStyle(fontWeight: FontWeight.w700)),
                  );
                },
              ),
            ],
          );
        },
      ),
    );
  }

  Future<void> _sendPasswordReset() async {
    final user = ref.read(authProvider).user!;
    final ok = await ref.read(authProvider.notifier).forgotPassword(user.email);
    if (!mounted) return;
    if (ok) {
      _showSnack('Email di ripristino inviata. Controlla la tua casella di posta.');
    }
  }

  void _showSnack(String msg) {
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(
      content: Text(msg),
      backgroundColor: const Color(0xFF1A1A1A),
      behavior: SnackBarBehavior.floating,
      shape: const RoundedRectangleBorder(borderRadius: BorderRadius.zero),
    ));
  }

  // ── Build ────────────────────────────────────────────────────────────────────

  @override
  Widget build(BuildContext context) {
    final auth = ref.watch(authProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFF8F6F1),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        title: const Text('Profilo',
            style: TextStyle(color: Color(0xFF1A1A1A), fontWeight: FontWeight.w700)),
      ),
      body: auth.isAuthenticated
          ? _buildProfile(auth)
          : _buildGuest(),
    );
  }

  Widget _buildProfile(AuthState auth) {
    final user = auth.user!;

    return ListView(
      children: [
        // ── Avatar + nome ──────────────────────────────────────────────────
        Container(
          color: Colors.white,
          padding: const EdgeInsets.all(20),
          child: Row(children: [
            CircleAvatar(
              radius: 30,
              backgroundColor: _red,
              child: Text(user.name[0].toUpperCase(),
                  style: const TextStyle(color: Colors.white, fontSize: 24, fontWeight: FontWeight.w700)),
            ),
            const SizedBox(width: 14),
            Expanded(
              child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                Text(user.name,
                    style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
                if (user.username != null)
                  Text('@${user.username}',
                      style: const TextStyle(fontSize: 12, color: Colors.black45)),
                Text(user.email,
                    style: const TextStyle(fontSize: 12, color: Colors.black45)),
                const SizedBox(height: 4),
                Row(children: [
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                    decoration: BoxDecoration(
                      color: user.emailVerified
                          ? const Color(0xFFF0FDF4)
                          : const Color(0xFFFFFBEB),
                      border: Border.all(
                          color: user.emailVerified
                              ? const Color(0xFF86EFAC)
                              : const Color(0xFFFCD34D)),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      user.emailVerified ? 'Email verificata' : 'Email non verificata',
                      style: TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.w600,
                        color: user.emailVerified
                            ? const Color(0xFF16A34A)
                            : const Color(0xFFB45309),
                      ),
                    ),
                  ),
                ]),
              ]),
            ),
          ]),
        ),

        const SizedBox(height: 8),

        // ── Sezione: Account ───────────────────────────────────────────────
        _sectionHeader('Account'),
        _tile(
          icon: Icons.person_outline,
          title: 'Dati personali',
          subtitle: 'Nome, username ed email',
          onTap: _showEditProfileDialog,
        ),
        _divider(),
        _tile(
          icon: Icons.lock_outline,
          title: 'Reimposta password',
          subtitle: 'Ricevi un link via email',
          onTap: _sendPasswordReset,
        ),

        const SizedBox(height: 8),

        // ── Sezione: Preferenze ────────────────────────────────────────────
        _sectionHeader('Preferenze'),
        _tile(
          icon: Icons.newspaper_outlined,
          title: 'Testate seguite',
          subtitle: '${user.preferredSources.length} testate selezionate',
          onTap: () => context.go('/categories'),
        ),
        _divider(),
        _tile(
          icon: Icons.rss_feed_outlined,
          title: 'Feed RSS personali',
          subtitle: 'Aggiungi e leggi feed RSS custom',
          onTap: () {
            ref.invalidate(userFeedsProvider);
            Navigator.push(context, MaterialPageRoute(
              builder: (_) => const UserFeedsScreen(),
            ));
          },
        ),

        const SizedBox(height: 8),

        // ── Logout ─────────────────────────────────────────────────────────
        Container(
          color: Colors.white,
          child: ListTile(
            leading: const Icon(Icons.logout, color: _red),
            title: const Text('Esci dall\'account',
                style: TextStyle(color: _red, fontWeight: FontWeight.w600)),
            onTap: () async {
              await ref.read(authProvider.notifier).logout();
              if (mounted) context.go('/');
            },
          ),
        ),

        const SizedBox(height: 8),
        Padding(
          padding: const EdgeInsets.all(16),
          child: Text('FlamingNews v1.0 · Notizie comparate da fonti diverse',
              style: TextStyle(fontSize: 11, color: Colors.grey.shade400),
              textAlign: TextAlign.center),
        ),
      ],
    );
  }

  Widget _buildGuest() {
    return Container(
      color: Colors.white,
      padding: const EdgeInsets.all(24),
      child: Column(children: [
        const Icon(Icons.person_outline, size: 56, color: Colors.grey),
        const SizedBox(height: 12),
        const Text('Accedi per utilizzare tutte le funzionalità',
            textAlign: TextAlign.center,
            style: TextStyle(fontSize: 14, color: Colors.black54)),
        const SizedBox(height: 16),
        SizedBox(
          width: double.infinity,
          child: ElevatedButton(
            onPressed: () => context.go('/login'),
            style: ElevatedButton.styleFrom(
              backgroundColor: _red,
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(vertical: 12),
              shape: const RoundedRectangleBorder(borderRadius: BorderRadius.zero),
            ),
            child: const Text('Accedi', style: TextStyle(fontWeight: FontWeight.w700)),
          ),
        ),
        const SizedBox(height: 8),
        TextButton(
          onPressed: () => context.go('/register'),
          child: const Text('Crea account', style: TextStyle(color: _red)),
        ),
      ]),
    );
  }

  Widget _sectionHeader(String label) => Padding(
    padding: const EdgeInsets.fromLTRB(16, 12, 16, 4),
    child: Text(label.toUpperCase(),
        style: TextStyle(fontSize: 11, fontWeight: FontWeight.w700,
            color: Colors.grey.shade500, letterSpacing: 0.8)),
  );

  Widget _tile({
    required IconData icon,
    required String title,
    required String subtitle,
    required VoidCallback onTap,
  }) =>
      Container(
        color: Colors.white,
        child: ListTile(
          leading: Icon(icon, color: Colors.black54, size: 22),
          title: Text(title, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
          subtitle: Text(subtitle, style: const TextStyle(fontSize: 12, color: Colors.black38)),
          trailing: const Icon(Icons.chevron_right, color: Colors.black26, size: 20),
          onTap: onTap,
        ),
      );

  Widget _divider() => const Divider(height: 1, indent: 56, color: Color(0xFFF3F4F6));
}
