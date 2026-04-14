import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../api/api_client.dart';
import '../models/topic.dart';

// Lista topic
class TopicsState {
  final List<TopicSummary> topics;
  final int currentPage;
  final int lastPage;
  final bool loading;
  final String? error;

  const TopicsState({
    this.topics = const [],
    this.currentPage = 1,
    this.lastPage = 1,
    this.loading = false,
    this.error,
  });

  TopicsState copyWith({
    List<TopicSummary>? topics,
    int? currentPage,
    int? lastPage,
    bool? loading,
    String? error,
  }) =>
      TopicsState(
        topics: topics ?? this.topics,
        currentPage: currentPage ?? this.currentPage,
        lastPage: lastPage ?? this.lastPage,
        loading: loading ?? this.loading,
        error: error,
      );
}

class TopicsNotifier extends StateNotifier<TopicsState> {
  final Ref _ref;

  TopicsNotifier(this._ref) : super(const TopicsState());

  Future<void> fetchTopics({int page = 1}) async {
    state = state.copyWith(loading: true, error: null);
    try {
      final dio = _ref.read(dioProvider);
      final response = await dio.get('/topics', queryParameters: {'page': page, 'per_page': 15});
      final data = response.data as Map<String, dynamic>;
      final items = (data['data'] as List<dynamic>)
          .map((e) => TopicSummary.fromJson(e as Map<String, dynamic>))
          .toList();
      final meta = data['meta'] as Map<String, dynamic>;
      state = state.copyWith(
        topics: items,
        currentPage: meta['current_page'] as int,
        lastPage: meta['last_page'] as int,
        loading: false,
      );
    } catch (e) {
      state = state.copyWith(loading: false, error: e.toString());
    }
  }
}

final topicsProvider = StateNotifierProvider<TopicsNotifier, TopicsState>(
  (ref) => TopicsNotifier(ref),
);

// Dettaglio topic singolo
final topicDetailProvider = FutureProvider.family<TopicDetail, int>((ref, id) async {
  final dio = ref.read(dioProvider);
  final response = await dio.get('/topics/$id');
  return TopicDetail.fromJson(response.data as Map<String, dynamic>);
});

// Provider per analisi AI
final topicAnalysisProvider = StateProvider.family<String?, int>((ref, id) => null);
