import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import '../../core/providers/auth_provider.dart';
import '../../core/providers/user_feeds_provider.dart';
import '../settings/user_feeds_screen.dart';

const _red = Color(0xFFC41E3A);

class MyFeedsScreen extends ConsumerStatefulWidget {
  const MyFeedsScreen({super.key});

  @override
  ConsumerState<MyFeedsScreen> createState() => _MyFeedsScreenState();
}

class _MyFeedsScreenState extends ConsumerState<MyFeedsScreen> {
  final _scrollCtrl = ScrollController();
  List<Map<String, dynamic>> _articles = [];
  bool _loading = true;
  bool _loadingMore = false;
  bool _refreshing = false;
  int _page = 1;
  bool _hasMore = false;

  @override
  void initState() {
    super.initState();
    _load(1);
    _scrollCtrl.addListener(() {
      if (_scrollCtrl.position.pixels > _scrollCtrl.position.maxScrollExtent - 400) {
        if (_hasMore && !_loadingMore) _load(_page + 1);
      }
    });
  }

  @override
  void dispose() {
    _scrollCtrl.dispose();
    super.dispose();
  }

  Future<void> _load(int page) async {
    if (page == 1) { setState(() => _loading = true); }
    else { setState(() => _loadingMore = true); }

    final result = await ref.read(userFeedsProvider.notifier)
        .fetchArticles(page: page);

    setState(() {
      if (page == 1) {
        _articles = result['data'];
      } else {
        _articles.addAll(result['data']);
      }
      _page    = result['meta']['current_page'] as int;
      _hasMore = _page < (result['meta']['last_page'] as int);
      _loading     = false;
      _loadingMore = false;
    });
  }

  Future<void> _refresh() async {
    setState(() => _refreshing = true);
    await _load(1);
    setState(() => _refreshing = false);
  }

  @override
  Widget build(BuildContext context) {
    final isAuth = ref.watch(authProvider).isAuthenticated;

    if (!isAuth) {
      return Scaffold(
        backgroundColor: const Color(0xFFF8F6F1),
        appBar: AppBar(
          backgroundColor: Colors.white,
          elevation: 0,
          title: const Text('Feed personali',
              style: TextStyle(color: Color(0xFF1A1A1A), fontWeight: FontWeight.w700)),
        ),
        body: Center(
          child: Padding(
            padding: const EdgeInsets.all(24),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(Icons.rss_feed_outlined, size: 56, color: Colors.grey),
                const SizedBox(height: 12),
                const Text('Accedi per aggiungere feed RSS personali',
                    textAlign: TextAlign.center,
                    style: TextStyle(fontSize: 14, color: Colors.black54)),
                const SizedBox(height: 16),
                ElevatedButton(
                  onPressed: () => context.go('/login'),
                  style: ElevatedButton.styleFrom(
                    backgroundColor: _red,
                    foregroundColor: Colors.white,
                    shape: const RoundedRectangleBorder(borderRadius: BorderRadius.zero),
                  ),
                  child: const Text('Accedi', style: TextStyle(fontWeight: FontWeight.w700)),
                ),
              ],
            ),
          ),
        ),
      );
    }

    return Scaffold(
      backgroundColor: const Color(0xFFF8F6F1),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        title: const Text('Feed personali',
            style: TextStyle(color: Color(0xFF1A1A1A), fontWeight: FontWeight.w700)),
        actions: [
          if (_refreshing)
            const Padding(
              padding: EdgeInsets.all(14),
              child: SizedBox(width: 20, height: 20,
                  child: CircularProgressIndicator(color: _red, strokeWidth: 2)),
            )
          else
            IconButton(
              icon: const Icon(Icons.refresh, color: Colors.black54),
              onPressed: _refresh,
            ),
          IconButton(
            icon: const Icon(Icons.tune_outlined, color: Colors.black54),
            tooltip: 'Gestisci feed',
            onPressed: () {
              ref.invalidate(userFeedsProvider);
              Navigator.push(context, MaterialPageRoute(
                builder: (_) => const UserFeedsScreen(),
              ));
            },
          ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: _red))
          : _articles.isEmpty
              ? _buildEmpty()
              : RefreshIndicator(
                  color: _red,
                  onRefresh: _refresh,
                  child: ListView.separated(
                    controller: _scrollCtrl,
                    physics: const AlwaysScrollableScrollPhysics(),
                    itemCount: _articles.length + (_loadingMore ? 1 : 0),
                    separatorBuilder: (_, __) =>
                        const Divider(height: 1, color: Color(0xFFF3F4F6)),
                    itemBuilder: (ctx, i) {
                      if (i == _articles.length) {
                        return const Padding(
                          padding: EdgeInsets.all(16),
                          child: Center(
                              child: CircularProgressIndicator(color: _red, strokeWidth: 2)),
                        );
                      }
                      return _ArticleTile(article: _articles[i]);
                    },
                  ),
                ),
      floatingActionButton: FloatingActionButton(
        backgroundColor: _red,
        foregroundColor: Colors.white,
        shape: const RoundedRectangleBorder(borderRadius: BorderRadius.zero),
        tooltip: 'Gestisci feed',
        onPressed: () {
          ref.invalidate(userFeedsProvider);
          Navigator.push(context, MaterialPageRoute(
            builder: (_) => const UserFeedsScreen(),
          ));
        },
        child: const Icon(Icons.rss_feed),
      ),
    );
  }

  Widget _buildEmpty() => Center(
    child: Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        const Icon(Icons.rss_feed_outlined, size: 56, color: Colors.grey),
        const SizedBox(height: 12),
        const Text('Nessun feed aggiunto',
            style: TextStyle(fontSize: 14, color: Colors.black54)),
        const SizedBox(height: 4),
        const Text('Aggiungi feed RSS per leggere le tue fonti preferite',
            style: TextStyle(fontSize: 12, color: Colors.black38),
            textAlign: TextAlign.center),
        const SizedBox(height: 16),
        ElevatedButton.icon(
          onPressed: () => Navigator.push(context, MaterialPageRoute(
            builder: (_) => const UserFeedsScreen(),
          )),
          style: ElevatedButton.styleFrom(
            backgroundColor: _red,
            foregroundColor: Colors.white,
            shape: const RoundedRectangleBorder(borderRadius: BorderRadius.zero),
          ),
          icon: const Icon(Icons.add, size: 18),
          label: const Text('Aggiungi feed', style: TextStyle(fontWeight: FontWeight.w700)),
        ),
      ],
    ),
  );
}

class _ArticleTile extends StatelessWidget {
  final Map<String, dynamic> article;
  const _ArticleTile({required this.article});

  @override
  Widget build(BuildContext context) {
    return Container(
      color: Colors.white,
      child: InkWell(
        onTap: () {
          final url = article['url'] as String?;
          if (url != null) {
            // L'apertura URL viene gestita dall'app host
          }
        },
        child: Padding(
          padding: const EdgeInsets.all(12),
          child: Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              if (article['url_to_image'] != null) ...[
                ClipRect(
                  child: Image.network(
                    article['url_to_image'] as String,
                    width: 76, height: 62, fit: BoxFit.cover,
                    errorBuilder: (_, __, ___) => const SizedBox(width: 76, height: 62),
                  ),
                ),
                const SizedBox(width: 12),
              ],
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(article['title'] ?? '',
                        style: const TextStyle(
                            fontSize: 14, fontWeight: FontWeight.w600, height: 1.3),
                        maxLines: 3,
                        overflow: TextOverflow.ellipsis),
                    const SizedBox(height: 4),
                    Row(children: [
                      if (article['source_name'] != null && article['source_name'] != '')
                        Text(article['source_name'] as String,
                            style: const TextStyle(
                                fontSize: 11, color: _red, fontWeight: FontWeight.w600)),
                      if (article['published_at'] != null) ...[
                        const SizedBox(width: 6),
                        Text(_formatDate(article['published_at'] as String),
                            style: const TextStyle(fontSize: 11, color: Colors.black38)),
                      ],
                    ]),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  String _formatDate(String str) {
    try {
      final d = DateTime.parse(str).toLocal();
      return '${d.day}/${d.month}/${d.year}';
    } catch (_) {
      return '';
    }
  }
}
