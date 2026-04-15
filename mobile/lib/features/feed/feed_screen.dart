import 'dart:async';
import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:google_fonts/google_fonts.dart';
import 'package:shimmer/shimmer.dart';
import '../../core/config/ads_config.dart';
import '../../core/providers/articles_provider.dart';
import '../../core/providers/auth_provider.dart';
import '../../shared/widgets/ad_banner.dart';
import 'article_card.dart';
import 'article_webview_screen.dart';

const _categories = [
  (null,          'Temi'),
  ('politica',    'Politica'),
  ('economia',    'Economia'),
  ('esteri',      'Esteri'),
  ('tecnologia',  'Tech'),
  ('sport',       'Sport'),
  ('cultura',     'Cultura'),
  ('generale',    'Generale'),
  ('scienza',     'Scienza'),
  ('salute',      'Salute'),
  ('ambiente',    'Ambiente'),
  ('istruzione',  'Istruzione'),
  ('cibo',        'Cibo'),
  ('viaggi',      'Viaggi'),
];

class FeedScreen extends ConsumerStatefulWidget {
  const FeedScreen({super.key});

  @override
  ConsumerState<FeedScreen> createState() => _FeedScreenState();
}

class _FeedScreenState extends ConsumerState<FeedScreen> {
  String? _activeCategory;

  // ── Ricerca ─────────────────────────────────────────────────────────────
  bool _searchActive = false;
  final _searchController = TextEditingController();
  Timer? _debounce;

  void _onSearchChanged(String q) {
    _debounce?.cancel();
    _debounce = Timer(const Duration(milliseconds: 350), () {
      if (q.isEmpty) {
        ref.read(articlesProvider.notifier).fetchArticles(category: _activeCategory);
      } else {
        ref.read(articlesProvider.notifier).fetchArticles(q: q, category: _activeCategory);
      }
    });
  }

  void _closeSearch() {
    _searchController.clear();
    _debounce?.cancel();
    setState(() => _searchActive = false);
    ref.read(articlesProvider.notifier).fetchArticles(category: _activeCategory);
  }

  @override
  void dispose() {
    _searchController.dispose();
    _debounce?.cancel();
    super.dispose();
  }

  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(articlesProvider.notifier).fetchArticles();
    });
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(articlesProvider);

    final auth = ref.watch(authProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFF8F6F1),
      appBar: PreferredSize(
        preferredSize: const Size.fromHeight(4 + kToolbarHeight + 48),
        child: Material(
          color: Colors.white,
          elevation: 1,
          shadowColor: Colors.black12,
          child: Column(
            mainAxisSize: MainAxisSize.min,
            children: [
              // ── Striscia rossa superiore ─────────────────────────
              Container(height: 4, color: const Color(0xFFC41E3A)),

              // ── Logo + auth  /  barra di ricerca ────────────────
              SizedBox(
                height: kToolbarHeight,
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  child: _searchActive
                      // ── Modalità ricerca ────────────────────────────
                      ? Row(
                          children: [
                            IconButton(
                              icon: const Icon(Icons.arrow_back, size: 20),
                              color: const Color(0xFF374151),
                              padding: EdgeInsets.zero,
                              constraints: const BoxConstraints(),
                              onPressed: _closeSearch,
                            ),
                            const SizedBox(width: 8),
                            Expanded(
                              child: TextField(
                                controller: _searchController,
                                autofocus: true,
                                onChanged: _onSearchChanged,
                                style: const TextStyle(fontSize: 15),
                                decoration: InputDecoration(
                                  hintText: 'Cerca articoli…',
                                  hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 15),
                                  border: InputBorder.none,
                                  isDense: true,
                                  contentPadding: EdgeInsets.zero,
                                ),
                              ),
                            ),
                            ValueListenableBuilder(
                              valueListenable: _searchController,
                              builder: (_, val, __) => val.text.isNotEmpty
                                  ? IconButton(
                                      icon: const Icon(Icons.close, size: 18),
                                      color: Colors.grey,
                                      padding: EdgeInsets.zero,
                                      constraints: const BoxConstraints(),
                                      onPressed: () {
                                        _searchController.clear();
                                        _onSearchChanged('');
                                      },
                                    )
                                  : const SizedBox.shrink(),
                            ),
                          ],
                        )
                      // ── Modalità normale ────────────────────────────
                      : Row(
                          children: [
                            Text.rich(TextSpan(children: [
                              TextSpan(
                                text: 'Flaming',
                                style: GoogleFonts.playfairDisplay(
                                  fontSize: 22,
                                  fontWeight: FontWeight.w800,
                                  color: const Color(0xFF1A1A1A),
                                ),
                              ),
                              TextSpan(
                                text: 'News',
                                style: GoogleFonts.playfairDisplay(
                                  fontSize: 22,
                                  fontWeight: FontWeight.w800,
                                  color: const Color(0xFFC41E3A),
                                ),
                              ),
                            ])),
                            const Spacer(),
                            IconButton(
                              icon: const Icon(Icons.search, size: 20),
                              color: const Color(0xFF374151),
                              padding: EdgeInsets.zero,
                              constraints: const BoxConstraints(),
                              onPressed: () => setState(() => _searchActive = true),
                            ),
                            const SizedBox(width: 12),
                            if (auth.user != null) ...[
                              Text(
                                auth.user!.name,
                                style: const TextStyle(fontSize: 11, color: Colors.grey),
                                overflow: TextOverflow.ellipsis,
                              ),
                              const SizedBox(width: 8),
                            ],
                            GestureDetector(
                              onTap: () => ref.read(authProvider.notifier).logout(),
                              child: Container(
                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                decoration: BoxDecoration(
                                  border: Border.all(color: Colors.grey.shade300),
                                ),
                                child: const Text(
                                  'ESCI',
                                  style: TextStyle(
                                    fontSize: 10,
                                    fontWeight: FontWeight.w700,
                                    letterSpacing: 0.8,
                                    color: Color(0xFF374151),
                                  ),
                                ),
                              ),
                            ),
                          ],
                        ),
                ),
              ),

              // ── Barra categorie — underline stile ────────────────
              Container(
                height: 48,
                decoration: const BoxDecoration(
                  border: Border(top: BorderSide(color: Color(0xFFF3F4F6))),
                ),
                child: ListView.builder(
                  scrollDirection: Axis.horizontal,
                  padding: const EdgeInsets.symmetric(horizontal: 8),
                  itemCount: _categories.length,
                  itemBuilder: (ctx, i) {
                    final cat = _categories[i];
                    final isActive = _activeCategory == cat.$1;
                    return GestureDetector(
                      onTap: () {
                        setState(() => _activeCategory = cat.$1);
                        ref.read(articlesProvider.notifier).fetchArticles(category: cat.$1);
                      },
                      child: AnimatedContainer(
                        duration: const Duration(milliseconds: 150),
                        padding: const EdgeInsets.symmetric(horizontal: 16),
                        decoration: BoxDecoration(
                          border: Border(
                            bottom: BorderSide(
                              color: isActive ? const Color(0xFFC41E3A) : Colors.transparent,
                              width: 2,
                            ),
                          ),
                        ),
                        child: Center(
                          child: Text(
                            cat.$2,
                            style: TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.w600,
                              color: isActive ? const Color(0xFFC41E3A) : Colors.black54,
                            ),
                          ),
                        ),
                      ),
                    );
                  },
                ),
              ),
            ],
          ),
        ),
      ),
      body: Builder(builder: (ctx) {
        if (state.loading) return _buildSkeleton();
        if (state.error != null) return _buildError(state.error!);
        if (state.articles.isEmpty) {
          return const Center(child: Text('Nessun articolo disponibile.'));
        }

        // Intercala un AdBanner ogni N articoli
        final freq = AdsConfig.feedAdFrequency;
        final itemCount = state.articles.length +
            (state.articles.length / freq).floor();

        // Aggiunge 1 slot finale: loading indicator o sentinel
        final extraSlots = 1;

        return NotificationListener<ScrollNotification>(
          onNotification: (notification) {
            if (notification is ScrollEndNotification) {
              final px = notification.metrics.pixels;
              final max = notification.metrics.maxScrollExtent;
              if (max > 0 && px >= max - 400) {
                ref.read(articlesProvider.notifier).nextPage();
              }
            }
            return false;
          },
          child: ListView.separated(
            itemCount: itemCount + extraSlots,
            separatorBuilder: (_, __) => const SizedBox(height: 1),
            itemBuilder: (ctx, i) {
              // Slot finale: spinner o spazio
              if (i == itemCount) {
                if (state.loadingMore) {
                  return const Padding(
                    padding: EdgeInsets.symmetric(vertical: 24),
                    child: Center(
                      child: SizedBox(
                        width: 20, height: 20,
                        child: CircularProgressIndicator(
                          strokeWidth: 2,
                          color: Color(0xFFC41E3A),
                        ),
                      ),
                    ),
                  );
                }
                return const SizedBox(height: 24);
              }

              // Ogni (freq + 1) slot il (freq)-esimo è un banner
              if ((i + 1) % (freq + 1) == 0) {
                return const Padding(
                  padding: EdgeInsets.symmetric(vertical: 8),
                  child: AdBanner(),
                );
              }
              // Indice reale nell'array articoli
              final articleIndex = i - (i / (freq + 1)).floor();
              if (articleIndex >= state.articles.length) {
                return const SizedBox.shrink();
              }
              final article = state.articles[articleIndex];
              return ArticleCard(
                article: article,
                onTap: () => Navigator.push(
                  context,
                  MaterialPageRoute(
                    builder: (_) => ArticleWebViewScreen(
                      url: article.url,
                      title: article.title,
                    ),
                  ),
                ),
                onLike: () => ref.read(articlesProvider.notifier).toggleLike(article.id),
              );
            },
          ),
        );
      }),
    );
  }

  Widget _buildSkeleton() {
    return Shimmer.fromColors(
      baseColor: Colors.grey.shade200,
      highlightColor: Colors.grey.shade100,
      child: ListView.builder(
        itemCount: 6,
        itemBuilder: (_, __) => Container(
          margin: const EdgeInsets.only(bottom: 1),
          height: 200,
          color: Colors.white,
        ),
      ),
    );
  }

  Widget _buildError(String error) {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(Icons.error_outline, color: Color(0xFFC41E3A), size: 40),
          const SizedBox(height: 12),
          Text(error, textAlign: TextAlign.center, style: const TextStyle(color: Colors.black54)),
          const SizedBox(height: 16),
          ElevatedButton(
            onPressed: () => ref.read(articlesProvider.notifier).fetchArticles(category: _activeCategory),
            style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFFC41E3A)),
            child: const Text('Riprova', style: TextStyle(color: Colors.white)),
          ),
        ],
      ),
    );
  }
}
