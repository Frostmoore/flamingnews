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
  final bool loadingMore;
  final String? error;

  const ArticlesState({
    this.articles = const [],
    this.currentPage = 1,
    this.lastPage = 1,
    this.loading = false,
    this.loadingMore = false,
    this.error,
  });

  bool get hasMore => currentPage < lastPage;

  ArticlesState copyWith({
    List<Article>? articles,
    int? currentPage,
    int? lastPage,
    bool? loading,
    bool? loadingMore,
    String? error,
  }) =>
      ArticlesState(
        articles: articles ?? this.articles,
        currentPage: currentPage ?? this.currentPage,
        lastPage: lastPage ?? this.lastPage,
        loading: loading ?? this.loading,
        loadingMore: loadingMore ?? this.loadingMore,
        error: error,
      );
}

class ArticlesNotifier extends StateNotifier<ArticlesState> {
  final Ref _ref;
  String? _activeCategory;
  String? _activeQuery;

  ArticlesNotifier(this._ref) : super(const ArticlesState());

  Future<void> fetchArticles({String? category, int page = 1, String? q}) async {
    _activeCategory = category;
    _activeQuery    = q;

    final appending = page > 1;
    if (appending) {
      state = state.copyWith(loadingMore: true, error: null);
    } else {
      state = state.copyWith(loading: true, error: null);
    }

    try {
      final dio = _ref.read(dioProvider);

      // ── Ricerca ──────────────────────────────────────────────
      if (q != null && q.isNotEmpty) {
        final params = <String, dynamic>{'q': q, 'page': page, 'per_page': 10};
        if (category != null) params['category'] = category;
        final response = await dio.get('/articles', queryParameters: params);
        final newItems = _parseItems(response);
        final meta    = response.data['meta'] as Map<String, dynamic>;
        state = state.copyWith(
          articles:    appending ? [...state.articles, ...newItems] : newItems,
          currentPage: meta['current_page'] as int,
          lastPage:    meta['last_page'] as int,
          loading:     false,
          loadingMore: false,
        );
        return;
      }

      // ── Tutte le categorie ───────────────────────────────────
      if (category == null) {
        final responses = await Future.wait(
          _allCategories.map((cat) => dio.get('/articles', queryParameters: {
            'category': cat, 'page': 1, 'per_page': _perCategory,
          })),
        );
        final items = responses
            .expand((r) => _parseItems(r))
            .toList()
          ..sort((a, b) => (b.publishedAt ?? DateTime(0)).compareTo(a.publishedAt ?? DateTime(0)));

        state = state.copyWith(
          articles:    items,
          currentPage: 1,
          lastPage:    1,
          loading:     false,
          loadingMore: false,
        );

      // ── Singola categoria ────────────────────────────────────
      } else {
        final response = await dio.get('/articles', queryParameters: {
          'page': page, 'per_page': 10, 'category': category,
        });
        final newItems = _parseItems(response);
        final meta    = response.data['meta'] as Map<String, dynamic>;

        state = state.copyWith(
          articles:    appending ? [...state.articles, ...newItems] : newItems,
          currentPage: meta['current_page'] as int,
          lastPage:    meta['last_page'] as int,
          loading:     false,
          loadingMore: false,
        );
      }
    } catch (e) {
      state = state.copyWith(loading: false, loadingMore: false, error: e.toString());
    }
  }

  List<Article> _parseItems(dynamic response) =>
      (response.data['data'] as List<dynamic>)
          .map((e) => Article.fromJson(e as Map<String, dynamic>))
          .toList();

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
    if (state.hasMore && !state.loadingMore && !state.loading) {
      await fetchArticles(
        category: _activeCategory,
        page:     state.currentPage + 1,
        q:        _activeQuery,
      );
    }
  }
}

final articlesProvider = StateNotifierProvider<ArticlesNotifier, ArticlesState>(
  (ref) => ArticlesNotifier(ref),
);
