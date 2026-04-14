import 'article.dart';

class TopicSummary {
  final int id;
  final String title;
  final List<String> keywords;
  final int articleCount;

  const TopicSummary({
    required this.id,
    required this.title,
    required this.keywords,
    required this.articleCount,
  });

  factory TopicSummary.fromJson(Map<String, dynamic> json) {
    return TopicSummary(
      id: json['id'] as int,
      title: json['title'] as String,
      keywords: (json['keywords'] as List<dynamic>?)?.cast<String>() ?? [],
      articleCount: json['article_count'] as int? ?? 0,
    );
  }
}

class TopicDetail {
  final int id;
  final String title;
  final List<String> keywords;
  final int articleCount;
  final String? aiAnalysis;
  final DateTime? aiGeneratedAt;
  final Map<String, List<Article>> sources;

  const TopicDetail({
    required this.id,
    required this.title,
    required this.keywords,
    required this.articleCount,
    this.aiAnalysis,
    this.aiGeneratedAt,
    required this.sources,
  });

  factory TopicDetail.fromJson(Map<String, dynamic> json) {
    final rawSources = json['sources'] as Map<String, dynamic>? ?? {};
    final sources = rawSources.map((lean, articles) {
      final list = (articles as List<dynamic>)
          .map((a) => Article.fromJson(a as Map<String, dynamic>))
          .toList();
      return MapEntry(lean, list);
    });

    return TopicDetail(
      id: json['id'] as int,
      title: json['title'] as String,
      keywords: (json['keywords'] as List<dynamic>?)?.cast<String>() ?? [],
      articleCount: json['article_count'] as int? ?? 0,
      aiAnalysis: json['ai_analysis'] as String?,
      aiGeneratedAt: json['ai_generated_at'] != null
          ? DateTime.tryParse(json['ai_generated_at'] as String)
          : null,
      sources: sources,
    );
  }
}
