class User {
  final int id;
  final String name;
  final String email;
  final String? avatar;
  final bool isPremium;
  final bool hasGoogle;
  final List<String> preferredCategories;

  const User({
    required this.id,
    required this.name,
    required this.email,
    this.avatar,
    required this.isPremium,
    required this.hasGoogle,
    required this.preferredCategories,
  });

  bool get needsCategories => preferredCategories.isEmpty;

  factory User.fromJson(Map<String, dynamic> json) {
    return User(
      id: json['id'] as int,
      name: json['name'] as String,
      email: json['email'] as String,
      avatar: json['avatar'] as String?,
      isPremium: json['is_premium'] as bool? ?? false,
      hasGoogle: json['has_google'] as bool? ?? false,
      preferredCategories:
          (json['preferred_categories'] as List<dynamic>?)?.cast<String>() ?? [],
    );
  }
}
