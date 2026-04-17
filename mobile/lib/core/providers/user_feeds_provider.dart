import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../api/api_client.dart';

class UserFeedModel {
  final int id;
  final String name;
  final String feedUrl;
  final int articlesCount;
  final String? lastFetchedAt;

  const UserFeedModel({
    required this.id,
    required this.name,
    required this.feedUrl,
    required this.articlesCount,
    this.lastFetchedAt,
  });

  factory UserFeedModel.fromJson(Map<String, dynamic> j) => UserFeedModel(
        id:            j['id'] as int,
        name:          j['name'] as String,
        feedUrl:       j['feed_url'] as String,
        articlesCount: j['articles_count'] as int? ?? 0,
        lastFetchedAt: j['last_fetched_at'] as String?,
      );

  UserFeedModel copyWith({int? articlesCount, String? lastFetchedAt}) => UserFeedModel(
        id:            id,
        name:          name,
        feedUrl:       feedUrl,
        articlesCount: articlesCount ?? this.articlesCount,
        lastFetchedAt: lastFetchedAt ?? this.lastFetchedAt,
      );
}

class UserFeedsState {
  final List<UserFeedModel> feeds;
  final bool loading;
  final int? refreshingId;
  final String? error;

  const UserFeedsState({
    this.feeds = const [],
    this.loading = false,
    this.refreshingId,
    this.error,
  });

  UserFeedsState copyWith({
    List<UserFeedModel>? feeds,
    bool? loading,
    int? refreshingId,
    bool clearRefreshing = false,
    String? error,
  }) =>
      UserFeedsState(
        feeds:        feeds ?? this.feeds,
        loading:      loading ?? this.loading,
        refreshingId: clearRefreshing ? null : (refreshingId ?? this.refreshingId),
        error:        error,
      );
}

class UserFeedsNotifier extends StateNotifier<UserFeedsState> {
  final Ref _ref;

  UserFeedsNotifier(this._ref) : super(const UserFeedsState()) {
    _load();
  }

  Future<void> _load() async {
    state = state.copyWith(loading: true);
    try {
      final res = await _ref.read(dioProvider).get('/my-feeds');
      final list = (res.data['data'] as List)
          .map((e) => UserFeedModel.fromJson(e as Map<String, dynamic>))
          .toList();
      state = state.copyWith(feeds: list, loading: false);
    } catch (_) {
      state = state.copyWith(loading: false);
    }
  }

  // Ritorna null se ok, stringa di errore altrimenti
  Future<String?> addFeed({required String name, required String feedUrl}) async {
    if (name.isEmpty || feedUrl.isEmpty) return 'Compila tutti i campi.';
    state = state.copyWith(loading: true);
    try {
      final res = await _ref.read(dioProvider).post('/my-feeds', data: {
        'name':     name,
        'feed_url': feedUrl,
      });
      final feed = UserFeedModel.fromJson(res.data['data'] as Map<String, dynamic>);
      state = state.copyWith(feeds: [feed, ...state.feeds], loading: false);
      return null;
    } catch (e) {
      state = state.copyWith(loading: false);
      final msg = _extractError(e);
      return msg;
    }
  }

  Future<void> deleteFeed(int id) async {
    try {
      await _ref.read(dioProvider).delete('/my-feeds/$id');
      state = state.copyWith(feeds: state.feeds.where((f) => f.id != id).toList());
    } catch (_) {}
  }

  Future<void> refreshFeed(int id) async {
    state = state.copyWith(refreshingId: id);
    try {
      final res = await _ref.read(dioProvider).post('/my-feeds/$id/refresh');
      final updated = UserFeedModel.fromJson(res.data['data'] as Map<String, dynamic>);
      state = state.copyWith(
        feeds: state.feeds.map((f) => f.id == id ? updated : f).toList(),
        clearRefreshing: true,
      );
    } catch (_) {
      state = state.copyWith(clearRefreshing: true);
    }
  }

  Future<Map<String, dynamic>> fetchArticles({int? feedId, int page = 1}) async {
    try {
      final params = <String, dynamic>{'page': page, 'per_page': 30};
      if (feedId != null) params['user_feed_id'] = feedId;
      final res = await _ref.read(dioProvider)
          .get('/my-feeds/articles', queryParameters: params);
      return {
        'data': List<Map<String, dynamic>>.from(res.data['data'] as List),
        'meta': res.data['meta'],
      };
    } catch (_) {
      return {'data': <Map<String, dynamic>>[], 'meta': {'current_page': 1, 'last_page': 1}};
    }
  }

  String _extractError(Object e) {
    try {
      final data = (e as dynamic).response?.data;
      return (data?['message'] as String?) ?? 'Errore sconosciuto.';
    } catch (_) {
      return 'Errore sconosciuto.';
    }
  }
}

final userFeedsProvider =
    StateNotifierProvider<UserFeedsNotifier, UserFeedsState>(
  (ref) => UserFeedsNotifier(ref),
);
