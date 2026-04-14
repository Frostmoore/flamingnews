import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../api/api_client.dart';
import '../models/article.dart';

const _allCategories = [
  'politica', 'economia', 'esteri', 'tecnologia',
  'sport', 'cultura', 'generale', 'scienza', 'salute',
  'ambiente', 'istruzione', 'cibo', 'viaggi',
];
const _perCategory = 6;

class ArticlesState {
  final List<Article> articles;
  final int currentPage;
  final int lastPage;
  final bool loading;
  final String? error;

  const ArticlesState({
    this.articles = const [],
    this.currentPage = 1,
    this.lastPage = 1,
    this.loading = false,
    this.error,
  });

  ArticlesState copyWith({
    List<Article>? articles,
    int? currentPage,
    int? lastPage,
    bool? loading,
    String? error,
  }) =>
      ArticlesState(
        articles: articles ?? this.articles,
        currentPage: currentPage ?? this.currentPage,
        lastPage: lastPage ?? this.lastPage,
        loading: loading ?? this.loading,
        error: error,
      );
}

class ArticlesNotifier extends StateNotifier<ArticlesState> {
  final Ref _ref;
  String? _activeCategory;

  ArticlesNotifier(this._ref) : super(const ArticlesState());

  Future<void> fetchArticles({String? category, int page = 1}) async {
    _activeCategory = category;
    state = state.copyWith(loading: true, error: null);

    try {
      final dio = _ref.read(dioProvider);

      if (category == null) {
        // Una chiamata per ogni categoria in parallelo
        final responses = await Future.wait(
          _allCategories.map(
            (cat) => dio.get('/articles', queryParameters: {
              'category': cat,
              'page': 1,
              'per_page': _perCategory,
            }),
          ),
        );

        // Unisci e ordina per data
        final items = responses
            .expand((r) => (r.data['data'] as List<dynamic>)
                .map((e) => Article.fromJson(e as Map<String, dynamic>)))
            .toList()
          ..sort((a, b) => (b.publishedAt ?? DateTime(0)).compareTo(a.publishedAt ?? DateTime(0)));

        state = state.copyWith(
          articles: items,
          currentPage: 1,
          lastPage: 1,
          loading: false,
        );
      } else {
        // Singola categoria: comportamento normale con paginazione
        final response = await dio.get('/articles', queryParameters: {
          'page': page,
          'per_page': 20,
          'category': category,
        });
        final data = response.data as Map<String, dynamic>;
        final items = (data['data'] as List<dynamic>)
            .map((e) => Article.fromJson(e as Map<String, dynamic>))
            .toList();
        final meta = data['meta'] as Map<String, dynamic>;

        state = state.copyWith(
          articles: items,
          currentPage: meta['current_page'] as int,
          lastPage: meta['last_page'] as int,
          loading: false,
        );
      }
    } catch (e) {
      state = state.copyWith(loading: false, error: e.toString());
    }
  }

  Future<void> toggleLike(int articleId) async {
    try {
      final dio = _ref.read(dioProvider);
      final res = await dio.post('/articles/$articleId/like');
      final liked      = res.data['liked'] as bool;
      final likesCount = res.data['likes_count'] as int;

      state = state.copyWith(
        articles: state.articles.map((a) {
          if (a.id == articleId) return a.copyWith(liked: liked, likesCount: likesCount);
          return a;
        }).toList(),
      );
    } catch (_) {}
  }

  Future<void> nextPage() async {
    if (state.currentPage < state.lastPage) {
      await fetchArticles(category: _activeCategory, page: state.currentPage + 1);
    }
  }
}

final articlesProvider = StateNotifierProvider<ArticlesNotifier, ArticlesState>(
  (ref) => ArticlesNotifier(ref),
);
