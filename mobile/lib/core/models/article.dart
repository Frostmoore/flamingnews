class CoverageSource {
  final int id;
  final String? title;
  final String? sourceName;
  final String? sourceDomain;
  final String url;
  final String? lean;

  const CoverageSource({
    required this.id,
    this.title,
    this.sourceName,
    this.sourceDomain,
    required this.url,
    this.lean,
  });

  factory CoverageSource.fromJson(Map<String, dynamic> json) {
    return CoverageSource(
      id:           json['id'] as int,
      title:        json['title'] as String?,
      sourceName:   json['source_name'] as String?,
      sourceDomain: json['source_domain'] as String?,
      url:          json['url'] as String,
      lean:         json['lean'] as String?,
    );
  }
}

class Article {
  final int id;
  final String title;
  final String? description;
  final String? content;
  final String url;
  final String? urlToImage;
  final String? sourceName;
  final String? sourceDomain;
  final String? author;
  final DateTime? publishedAt;
  final String category;
  final String? politicalLean;
  final int? topicId;
  final List<CoverageSource> coverage;
  final bool liked;
  final int likesCount;

  const Article({
    required this.id,
    required this.title,
    this.description,
    this.content,
    required this.url,
    this.urlToImage,
    this.sourceName,
    this.sourceDomain,
    this.author,
    this.publishedAt,
    required this.category,
    this.politicalLean,
    this.topicId,
    this.coverage = const [],
    this.liked = false,
    this.likesCount = 0,
  });

  Article copyWith({bool? liked, int? likesCount}) => Article(
        id: id,
        title: title,
        description: description,
        content: content,
        url: url,
        urlToImage: urlToImage,
        sourceName: sourceName,
        sourceDomain: sourceDomain,
        author: author,
        publishedAt: publishedAt,
        category: category,
        politicalLean: politicalLean,
        topicId: topicId,
        coverage: coverage,
        liked: liked ?? this.liked,
        likesCount: likesCount ?? this.likesCount,
      );

  factory Article.fromJson(Map<String, dynamic> json) {
    return Article(
      id:           json['id'] as int,
      title:        json['title'] as String,
      description:  json['description'] as String?,
      content:      json['content'] as String?,
      url:          json['url'] as String,
      urlToImage:   json['url_to_image'] as String?,
      sourceName:   json['source_name'] as String?,
      sourceDomain: json['source_domain'] as String?,
      author:       json['author'] as String?,
      publishedAt:  json['published_at'] != null
          ? DateTime.tryParse(json['published_at'] as String)
          : null,
      category:     json['category'] as String? ?? 'generale',
      politicalLean:json['political_lean'] as String?,
      topicId:      json['topic_id'] as int?,
      coverage:     (json['coverage'] as List<dynamic>?)
          ?.map((e) => CoverageSource.fromJson(e as Map<String, dynamic>))
          .toList() ?? [],
      liked:      json['liked'] as bool? ?? false,
      likesCount: json['likes_count'] as int? ?? 0,
    );
  }
}
