class User {
  final int id;
  final String name;
  final String? username;
  final String email;
  final String? avatar;
  final bool isPremium;
  final bool hasGoogle;
  final bool emailVerified;
  final List<String> preferredCategories;
  final List<String> preferredSources;

  const User({
    required this.id,
    required this.name,
    this.username,
    required this.email,
    this.avatar,
    required this.isPremium,
    required this.hasGoogle,
    required this.emailVerified,
    required this.preferredCategories,
    required this.preferredSources,
  });

  bool get needsSources => preferredSources.isEmpty;
  // backward compat
  bool get needsCategories => needsSources;

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id:                  json['id'] as int,
      name:                json['name'] as String,
      username:            json['username'] as String?,
      email:               json['email'] as String,
      avatar:              json['avatar'] as String?,
      isPremium:           json['is_premium'] as bool? ?? false,
      hasGoogle:           json['has_google'] as bool? ?? false,
      emailVerified:       json['email_verified'] as bool? ?? false,
      preferredCategories: (json['preferred_categories'] as List<dynamic>?)?.cast<String>() ?? [],
      preferredSources:    (json['preferred_sources']    as List<dynamic>?)?.cast<String>() ?? [],
    );
  }
}
