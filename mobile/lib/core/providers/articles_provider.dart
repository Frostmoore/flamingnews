import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../api/api_client.dart';
import '../models/article.dart';

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
  String? _activeCategory; // null = Temi, '__all__' = Tutte, string = categoria
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
      final params = <String, dynamic>{'page': page, 'per_page': 20};

      if (q != null && q.isNotEmpty) {
        // Ricerca
        params['q'] = q;
        if (category != null && category != '__all__') params['category'] = category;
      } else if (category == '__all__') {
        // Tab "Tutte": tutti gli articoli
        params['tab'] = 'tutte';
      } else if (category != null) {
        // Singola categoria
        params['category'] = category;
      }
      // else: null → Tab "Temi", backend filtra 4+ testate

      final response = await dio.get('/articles', queryParameters: params);
      final newItems = _parseItems(response);
      final meta = response.data['meta'] as Map<String, dynamic>;

      state = state.copyWith(
        articles:    appending ? [...state.articles, ...newItems] : newItems,
        currentPage: meta['current_page'] as int,
        lastPage:    meta['last_page'] as int,
        loading:     false,
        loadingMore: false,
      );
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
