import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../core/providers/auth_provider.dart';

class SettingsScreen extends ConsumerWidget {
  const SettingsScreen({super.key});

  @override
  Widget build(BuildContext context, WidgetRef ref) {
    final auth = ref.watch(authProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFF8F6F1),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        title: const Text('Profilo', style: TextStyle(color: Color(0xFF1A1A1A), fontWeight: FontWeight.w700)),
      ),
      body: ListView(
        children: [
          if (auth.isAuthenticated) ...[
            Container(
              padding: const EdgeInsets.all(20),
              color: Colors.white,
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(children: [
                    CircleAvatar(
                      radius: 28,
                      backgroundColor: const Color(0xFFC41E3A),
                      child: Text(
                        auth.user!.name[0].toUpperCase(),
                        style: const TextStyle(color: Colors.white, fontSize: 22, fontWeight: FontWeight.w700),
                      ),
                    ),
                    const SizedBox(width: 14),
                    Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      Text(auth.user!.name, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w700)),
                      Text(auth.user!.email, style: const TextStyle(fontSize: 12, color: Colors.grey)),
                    ]),
                  ]),
                  const SizedBox(height: 12),
                  Container(
                    padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                    decoration: BoxDecoration(
                      color: auth.isPremium ? const Color(0xFFFEF3C7) : Colors.grey.shade100,
                      borderRadius: BorderRadius.circular(4),
                    ),
                    child: Text(
                      auth.isPremium ? '★ Piano Premium' : 'Piano Free',
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w600,
                        color: auth.isPremium ? const Color(0xFFB45309) : Colors.grey,
                      ),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 8),
            ListTile(
              tileColor: Colors.white,
              leading: const Icon(Icons.logout, color: Color(0xFFC41E3A)),
              title: const Text('Esci', style: TextStyle(color: Color(0xFFC41E3A), fontWeight: FontWeight.w600)),
              onTap: () async {
                await ref.read(authProvider.notifier).logout();
                if (context.mounted) context.go('/');
              },
            ),
          ] else ...[
            Container(
              padding: const EdgeInsets.all(24),
              color: Colors.white,
              child: Column(
                children: [
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
                        backgroundColor: const Color(0xFFC41E3A),
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 12),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.zero),
                      ),
                      child: const Text('Accedi', style: TextStyle(fontWeight: FontWeight.w700)),
                    ),
                  ),
                  const SizedBox(height: 8),
                  TextButton(
                    onPressed: () => context.go('/register'),
                    child: const Text('Crea account', style: TextStyle(color: Color(0xFFC41E3A))),
                  ),
                ],
              ),
            ),
          ],
          const SizedBox(height: 8),
          ListTile(
            tileColor: Colors.white,
            leading: const Icon(Icons.info_outline),
            title: const Text('FlamingNews v1.0'),
            subtitle: const Text('Notizie comparate da fonti diverse'),
          ),
        ],
      ),
    );
  }
}
