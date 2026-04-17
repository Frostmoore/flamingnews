import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../core/api/api_client.dart';
import '../../core/providers/auth_provider.dart';

const _red = Color(0xFFC41E3A);

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

class CategoriesScreen extends ConsumerStatefulWidget {
  const CategoriesScreen({super.key});

  @override
  ConsumerState<CategoriesScreen> createState() => _CategoriesScreenState();
}

class _CategoriesScreenState extends ConsumerState<CategoriesScreen> {
  List<Map<String, dynamic>> _sources = [];
  List<String> _selected = [];
  bool _loadingSources = false;
  String? _localError;

  @override
  void initState() {
    super.initState();
    // Pre-seleziona le testate già salvate
    _selected = List<String>.from(
      ref.read(authProvider).user?.preferredSources ?? [],
    );
    _loadSources();
  }

  Future<void> _loadSources() async {
    setState(() => _loadingSources = true);
    try {
      final dio = ref.read(dioProvider);
      final res = await dio.get('/sources');
      setState(() {
        _sources = (res.data as List).cast<Map<String, dynamic>>();
        // Se l'utente non ha ancora testate salvate, pre-seleziona tutte
        if (_selected.isEmpty) {
          _selected.addAll(_sources.map((s) => s['domain'] as String));
        }
      });
    } catch (_) {
      setState(() => _localError = 'Impossibile caricare le testate. Riprova.');
    } finally {
      setState(() => _loadingSources = false);
    }
  }

  void _toggle(String domain) {
    setState(() {
      _selected.contains(domain)
          ? _selected.remove(domain)
          : _selected.add(domain);
    });
  }

  Future<void> _save() async {
    final ok = await ref.read(authProvider.notifier).updateSources(_selected);
    if (ok && mounted) context.go('/');
  }

  @override
  Widget build(BuildContext context) {
    final authState = ref.watch(authProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFF8F6F1),
      body: SafeArea(
        child: Padding(
          padding: const EdgeInsets.all(24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              const Text(
                'FlamingNews',
                style: TextStyle(
                  fontSize: 24,
                  fontWeight: FontWeight.w800,
                  color: _red,
                ),
              ),
              const SizedBox(height: 28),
              const Text(
                'Scegli le testate',
                style: TextStyle(fontSize: 22, fontWeight: FontWeight.w800, color: Color(0xFF1A1A1A)),
              ),
              const SizedBox(height: 6),
              const Text(
                'Seleziona le fonti RSS da cui vuoi ricevere notizie. Potrai cambiarle dal profilo.',
                style: TextStyle(fontSize: 14, color: Colors.black54, height: 1.4),
              ),
              const SizedBox(height: 16),

              if (_localError != null || authState.error != null)
                Container(
                  width: double.infinity,
                  margin: const EdgeInsets.only(bottom: 12),
                  padding: const EdgeInsets.all(12),
                  color: const Color(0xFFFEF2F2),
                  child: Text(
                    _localError ?? authState.error!,
                    style: const TextStyle(color: Color(0xFFDC2626), fontSize: 13),
                  ),
                ),

              // Tutte / Nessuna
              Row(
                children: [
                  TextButton(
                    onPressed: _loadingSources ? null : () => setState(() {
                      _selected = _sources.map((s) => s['domain'] as String).toList();
                    }),
                    child: const Text('Tutte',
                        style: TextStyle(color: _red, fontWeight: FontWeight.w700, fontSize: 13)),
                  ),
                  TextButton(
                    onPressed: _loadingSources ? null : () => setState(() => _selected.clear()),
                    child: Text('Nessuna',
                        style: TextStyle(color: Colors.grey.shade600, fontSize: 13)),
                  ),
                  const Spacer(),
                  Text(
                    '${_selected.length} selezionate',
                    style: const TextStyle(fontSize: 12, color: Colors.black45),
                  ),
                ],
              ),
              const SizedBox(height: 8),

              Expanded(
                child: _loadingSources
                    ? const Center(child: CircularProgressIndicator(color: _red, strokeWidth: 2))
                    : _sources.isEmpty
                        ? Center(
                            child: TextButton(
                              onPressed: _loadSources,
                              child: const Text('Riprova', style: TextStyle(color: _red)),
                            ),
                          )
                        : ListView.separated(
                            itemCount: _sources.length,
                            separatorBuilder: (_, __) =>
                                Divider(height: 1, color: Colors.grey.shade200),
                            itemBuilder: (context, i) {
                              final src    = _sources[i];
                              final domain = src['domain'] as String;
                              final name   = src['name'] as String? ?? domain;
                              final lean   = src['political_lean'] as String?;
                              final sel    = _selected.contains(domain);

                              return InkWell(
                                onTap: () => _toggle(domain),
                                child: Padding(
                                  padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 10),
                                  child: Row(
                                    children: [
                                      // Orientamento politico
                                      Container(
                                        width: 10,
                                        height: 10,
                                        margin: const EdgeInsets.only(right: 10),
                                        decoration: BoxDecoration(
                                          color: _leanColor(lean),
                                          shape: BoxShape.circle,
                                        ),
                                      ),

                                      // Nome + dominio
                                      Expanded(
                                        child: Column(
                                          crossAxisAlignment: CrossAxisAlignment.start,
                                          children: [
                                            Text(name,
                                                style: const TextStyle(
                                                    fontSize: 14, fontWeight: FontWeight.w600)),
                                            Text(domain,
                                                style: TextStyle(
                                                    fontSize: 11, color: Colors.grey.shade500)),
                                          ],
                                        ),
                                      ),

                                      // RSS badge
                                      Container(
                                        padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
                                        decoration: BoxDecoration(
                                          color: const Color(0xFFFEF2F2),
                                          border: Border.all(color: _red.withValues(alpha: 0.3)),
                                          borderRadius: BorderRadius.circular(3),
                                        ),
                                        child: const Text('RSS',
                                            style: TextStyle(
                                                fontSize: 9,
                                                fontWeight: FontWeight.w800,
                                                color: _red)),
                                      ),
                                      const SizedBox(width: 10),

                                      // Check
                                      Icon(
                                        sel ? Icons.check_circle : Icons.circle_outlined,
                                        color: sel ? _red : Colors.grey.shade300,
                                        size: 22,
                                      ),
                                    ],
                                  ),
                                ),
                              );
                            },
                          ),
              ),

              const SizedBox(height: 16),

              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: (authState.loading || _selected.isEmpty) ? null : _save,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: _red,
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.zero),
                    disabledBackgroundColor: Colors.grey.shade300,
                  ),
                  child: authState.loading
                      ? const SizedBox(
                          width: 20, height: 20,
                          child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                        )
                      : const Text('Personalizza il mio feed →',
                          style: TextStyle(fontWeight: FontWeight.w700, fontSize: 15)),
                ),
              ),
              TextButton(
                onPressed: authState.loading ? null : () => context.go('/'),
                child: const Text('Salta per ora',
                    style: TextStyle(color: Colors.black45, fontSize: 13)),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
