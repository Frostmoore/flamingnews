import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:shimmer/shimmer.dart';
import '../../core/config/ads_config.dart';
import '../../core/providers/articles_provider.dart';
import '../../shared/widgets/ad_banner.dart';
import 'article_card.dart';
import 'article_webview_screen.dart';

const _categories = [
  (null,          'Tutte'),
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

    return Scaffold(
      backgroundColor: const Color(0xFFF8F6F1),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        centerTitle: false,
        title: const Text(
          'FlamingNews',
          style: TextStyle(
            color: Color(0xFFC41E3A),
            fontWeight: FontWeight.w800,
            fontSize: 22,
          ),
        ),
        bottom: PreferredSize(
          preferredSize: const Size.fromHeight(48),
          child: SizedBox(
            height: 48,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
              itemCount: _categories.length,
              separatorBuilder: (_, __) => const SizedBox(width: 6),
              itemBuilder: (ctx, i) {
                final cat = _categories[i];
                final isActive = _activeCategory == cat.$1;
                return GestureDetector(
                  onTap: () {
                    setState(() => _activeCategory = cat.$1);
                    ref.read(articlesProvider.notifier).fetchArticles(category: cat.$1);
                  },
                  child: AnimatedContainer(
                    duration: const Duration(milliseconds: 180),
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 4),
                    decoration: BoxDecoration(
                      color: isActive ? const Color(0xFFC41E3A) : Colors.white,
                      borderRadius: BorderRadius.circular(20),
                      border: Border.all(
                        color: isActive ? const Color(0xFFC41E3A) : Colors.grey.shade300,
                      ),
                    ),
                    child: Text(
                      cat.$2,
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w600,
                        color: isActive ? Colors.white : Colors.black54,
                      ),
                    ),
                  ),
                );
              },
            ),
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

        return ListView.separated(
          itemCount: itemCount,
          separatorBuilder: (_, __) => const SizedBox(height: 1),
          itemBuilder: (ctx, i) {
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
