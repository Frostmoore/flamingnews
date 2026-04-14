import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../core/providers/auth_provider.dart';

const _allCategories = [
  ('politica',   'Politica',    '🏛️'),
  ('economia',   'Economia',    '📈'),
  ('esteri',     'Esteri',      '🌍'),
  ('tecnologia', 'Tecnologia',  '💻'),
  ('sport',      'Sport',       '⚽'),
  ('cultura',    'Cultura',     '🎭'),
  ('generale',   'Generale',    '🗞️'),
  ('scienza',    'Scienza',     '🔬'),
  ('salute',     'Salute',      '🏥'),
  ('ambiente',   'Ambiente',    '🌿'),
  ('istruzione', 'Istruzione',  '📚'),
  ('cibo',       'Cibo',        '🍕'),
  ('viaggi',     'Viaggi',      '✈️'),
];

class CategoriesScreen extends ConsumerStatefulWidget {
  const CategoriesScreen({super.key});

  @override
  ConsumerState<CategoriesScreen> createState() => _CategoriesScreenState();
}

class _CategoriesScreenState extends ConsumerState<CategoriesScreen> {
  late List<String> _selected;

  @override
  void initState() {
    super.initState();
    // Pre-seleziona le categorie già salvate (es. dopo Google login)
    _selected = List<String>.from(
      ref.read(authProvider).user?.preferredCategories ?? [],
    );
  }

  Future<void> _save() async {
    final ok = await ref.read(authProvider.notifier).updateCategories(_selected);
    if (ok && mounted) context.go('/');
  }

  @override
  Widget build(BuildContext context) {
    final loading = ref.watch(authProvider).loading;
    final error   = ref.watch(authProvider).error;

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
                  color: Color(0xFFC41E3A),
                ),
              ),
              const SizedBox(height: 28),
              const Text(
                'Cosa vuoi leggere?',
                style: TextStyle(fontSize: 22, fontWeight: FontWeight.w800, color: Color(0xFF1A1A1A)),
              ),
              const SizedBox(height: 6),
              const Text(
                'Scegli almeno un tema per personalizzare il tuo feed. Potrai cambiarli dal profilo.',
                style: TextStyle(fontSize: 14, color: Colors.black54, height: 1.4),
              ),
              const SizedBox(height: 24),

              if (error != null)
                Container(
                  width: double.infinity,
                  margin: const EdgeInsets.only(bottom: 16),
                  padding: const EdgeInsets.all(12),
                  color: const Color(0xFFFEF2F2),
                  child: Text(error, style: const TextStyle(color: Color(0xFFDC2626), fontSize: 13)),
                ),

              // Griglia categorie
              Expanded(
                child: GridView.count(
                  crossAxisCount: 2,
                  crossAxisSpacing: 10,
                  mainAxisSpacing: 10,
                  childAspectRatio: 1.4,
                  children: _allCategories.map((cat) {
                    final isSelected = _selected.contains(cat.$1);
                    return GestureDetector(
                      onTap: () => setState(() {
                        isSelected ? _selected.remove(cat.$1) : _selected.add(cat.$1);
                      }),
                      child: AnimatedContainer(
                        duration: const Duration(milliseconds: 150),
                        decoration: BoxDecoration(
                          color: isSelected ? const Color(0xFFFEF2F2) : Colors.white,
                          border: Border.all(
                            color: isSelected ? const Color(0xFFC41E3A) : Colors.grey.shade200,
                            width: isSelected ? 2 : 1,
                          ),
                        ),
                        child: Column(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Text(cat.$3, style: const TextStyle(fontSize: 26)),
                            const SizedBox(height: 6),
                            Text(
                              cat.$2,
                              style: TextStyle(
                                fontSize: 13,
                                fontWeight: FontWeight.w700,
                                color: isSelected ? const Color(0xFFC41E3A) : const Color(0xFF1A1A1A),
                              ),
                            ),
                            const SizedBox(height: 4),
                            AnimatedOpacity(
                              opacity: isSelected ? 1 : 0,
                              duration: const Duration(milliseconds: 150),
                              child: Container(
                                width: 20,
                                height: 20,
                                decoration: const BoxDecoration(
                                  color: Color(0xFFC41E3A),
                                  shape: BoxShape.circle,
                                ),
                                child: const Icon(Icons.check, color: Colors.white, size: 13),
                              ),
                            ),
                          ],
                        ),
                      ),
                    );
                  }).toList(),
                ),
              ),

              const SizedBox(height: 16),
              Text(
                '${_selected.length} / ${_allCategories.length} selezionate',
                style: const TextStyle(fontSize: 12, color: Colors.black45),
                textAlign: TextAlign.center,
              ),
              const SizedBox(height: 12),

              SizedBox(
                width: double.infinity,
                child: ElevatedButton(
                  onPressed: (loading || _selected.isEmpty) ? null : _save,
                  style: ElevatedButton.styleFrom(
                    backgroundColor: const Color(0xFFC41E3A),
                    foregroundColor: Colors.white,
                    padding: const EdgeInsets.symmetric(vertical: 16),
                    shape: RoundedRectangleBorder(borderRadius: BorderRadius.zero),
                    disabledBackgroundColor: Colors.grey.shade300,
                  ),
                  child: loading
                      ? const SizedBox(
                          width: 20, height: 20,
                          child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2),
                        )
                      : const Text('Personalizza il mio feed →',
                          style: TextStyle(fontWeight: FontWeight.w700, fontSize: 15)),
                ),
              ),
              TextButton(
                onPressed: loading ? null : () => context.go('/'),
                child: const Text('Salta per ora', style: TextStyle(color: Colors.black45, fontSize: 13)),
              ),
            ],
          ),
        ),
      ),
    );
  }
}
