import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import '../../core/providers/user_feeds_provider.dart';

const _red = Color(0xFFC41E3A);

class UserFeedsScreen extends ConsumerStatefulWidget {
  const UserFeedsScreen({super.key});

  @override
  ConsumerState<UserFeedsScreen> createState() => _UserFeedsScreenState();
}

class _UserFeedsScreenState extends ConsumerState<UserFeedsScreen> {
  final _nameCtrl = TextEditingController();
  final _urlCtrl  = TextEditingController();

  @override
  void dispose() {
    _nameCtrl.dispose();
    _urlCtrl.dispose();
    super.dispose();
  }

  Future<void> _showAddDialog() async {
    _nameCtrl.clear();
    _urlCtrl.clear();
    String? error;

    await showDialog(
      context: context,
      builder: (ctx) => StatefulBuilder(
        builder: (ctx, setS) => AlertDialog(
          backgroundColor: Colors.white,
          shape: const RoundedRectangleBorder(borderRadius: BorderRadius.zero),
          title: const Text('Aggiungi feed RSS',
              style: TextStyle(fontSize: 16, fontWeight: FontWeight.w800)),
          content: SingleChildScrollView(
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                if (error != null) ...[
                  Container(
                    padding: const EdgeInsets.all(10),
                    color: const Color(0xFFFEF2F2),
                    child: Text(error!,
                        style: const TextStyle(color: Color(0xFFDC2626), fontSize: 12)),
                  ),
                  const SizedBox(height: 12),
                ],
                TextField(
                  controller: _nameCtrl,
                  decoration: const InputDecoration(
                    labelText: 'Nome feed',
                    hintText: 'Es. Il mio blog preferito',
                    border: OutlineInputBorder(borderRadius: BorderRadius.zero),
                    focusedBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.zero,
                      borderSide: BorderSide(color: _red),
                    ),
                  ),
                ),
                const SizedBox(height: 12),
                TextField(
                  controller: _urlCtrl,
                  keyboardType: TextInputType.url,
                  decoration: const InputDecoration(
                    labelText: 'URL feed RSS',
                    hintText: 'https://esempio.it/feed',
                    border: OutlineInputBorder(borderRadius: BorderRadius.zero),
                    focusedBorder: OutlineInputBorder(
                      borderRadius: BorderRadius.zero,
                      borderSide: BorderSide(color: _red),
                    ),
                  ),
                ),
              ],
            ),
          ),
          actions: [
            TextButton(
              onPressed: () => Navigator.pop(ctx),
              child: const Text('Annulla', style: TextStyle(color: Colors.black45)),
            ),
            Consumer(
              builder: (_, ref2, __) {
                final loading = ref2.watch(userFeedsProvider).loading;
                return ElevatedButton(
                  style: ElevatedButton.styleFrom(
                    backgroundColor: _red,
                    foregroundColor: Colors.white,
                    shape: const RoundedRectangleBorder(borderRadius: BorderRadius.zero),
                  ),
                  onPressed: loading ? null : () async {
                    final nav = Navigator.of(ctx);
                    final err = await ref.read(userFeedsProvider.notifier).addFeed(
                      name: _nameCtrl.text.trim(),
                      feedUrl: _urlCtrl.text.trim(),
                    );
                    if (err == null) {
                      nav.pop();
                    } else {
                      setS(() => error = err);
                    }
                  },
                  child: loading
                      ? const SizedBox(width: 16, height: 16,
                          child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                      : const Text('Aggiungi', style: TextStyle(fontWeight: FontWeight.w700)),
                );
              },
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(userFeedsProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFF8F6F1),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        title: const Text('Feed RSS personali',
            style: TextStyle(color: Color(0xFF1A1A1A), fontWeight: FontWeight.w700)),
        actions: [
          IconButton(
            icon: const Icon(Icons.add, color: _red),
            onPressed: _showAddDialog,
          ),
        ],
      ),
      body: state.feeds.isEmpty && !state.loading
          ? _buildEmpty()
          : state.loading
              ? const Center(child: CircularProgressIndicator(color: _red))
              : _buildList(state),
    );
  }

  Widget _buildEmpty() => Center(
    child: Column(
      mainAxisSize: MainAxisSize.min,
      children: [
        const Icon(Icons.rss_feed_outlined, size: 48, color: Colors.grey),
        const SizedBox(height: 12),
        const Text('Nessun feed aggiunto',
            style: TextStyle(fontSize: 14, color: Colors.black54)),
        const SizedBox(height: 16),
        ElevatedButton.icon(
          onPressed: _showAddDialog,
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

  Widget _buildList(UserFeedsState state) => ListView.separated(
    itemCount: state.feeds.length,
    separatorBuilder: (_, __) => const Divider(height: 1, color: Color(0xFFF3F4F6)),
    itemBuilder: (ctx, i) {
      final feed = state.feeds[i];
      return Container(
        color: Colors.white,
        child: ListTile(
          leading: const Icon(Icons.rss_feed, color: _red, size: 22),
          title: Text(feed.name,
              style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
          subtitle: Text(
            '${feed.articlesCount} articoli · ${feed.feedUrl}',
            style: const TextStyle(fontSize: 11, color: Colors.black38),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
          trailing: Row(
            mainAxisSize: MainAxisSize.min,
            children: [
              IconButton(
                icon: state.refreshingId == feed.id
                    ? const SizedBox(width: 18, height: 18,
                        child: CircularProgressIndicator(color: _red, strokeWidth: 2))
                    : const Icon(Icons.refresh, color: Colors.black38, size: 20),
                onPressed: state.refreshingId == feed.id ? null : () =>
                    ref.read(userFeedsProvider.notifier).refreshFeed(feed.id),
              ),
              IconButton(
                icon: const Icon(Icons.delete_outline, color: Colors.redAccent, size: 20),
                onPressed: () => _confirmDelete(feed.id, feed.name),
              ),
            ],
          ),
          onTap: () => Navigator.push(ctx, MaterialPageRoute(
            builder: (_) => UserFeedArticlesScreen(feedId: feed.id, feedName: feed.name),
          )),
        ),
      );
    },
  );

  Future<void> _confirmDelete(int id, String name) async {
    final ok = await showDialog<bool>(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: Colors.white,
        shape: const RoundedRectangleBorder(borderRadius: BorderRadius.zero),
        title: const Text('Rimuovi feed', style: TextStyle(fontWeight: FontWeight.w800)),
        content: Text('Rimuovere "$name"? Verranno eliminati anche tutti gli articoli.'),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx, false),
              child: const Text('Annulla', style: TextStyle(color: Colors.black45))),
          TextButton(onPressed: () => Navigator.pop(ctx, true),
              child: const Text('Rimuovi', style: TextStyle(color: _red, fontWeight: FontWeight.w700))),
        ],
      ),
    );
    if (ok == true) ref.read(userFeedsProvider.notifier).deleteFeed(id);
  }
}

// ── Schermata articoli del feed ──────────────────────────────────────────────

class UserFeedArticlesScreen extends ConsumerStatefulWidget {
  final int feedId;
  final String feedName;
  const UserFeedArticlesScreen({super.key, required this.feedId, required this.feedName});

  @override
  ConsumerState<UserFeedArticlesScreen> createState() => _UserFeedArticlesScreenState();
}

class _UserFeedArticlesScreenState extends ConsumerState<UserFeedArticlesScreen> {
  final _scrollCtrl = ScrollController();
  List<Map<String, dynamic>> _articles = [];
  bool _loading = true;
  bool _loadingMore = false;
  int _page = 1;
  bool _hasMore = false;
  bool _refreshing = false;

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

    final items = await ref.read(userFeedsProvider.notifier)
        .fetchArticles(feedId: widget.feedId, page: page);

    setState(() {
      if (page == 1) { _articles = items['data']; }
      else { _articles.addAll(items['data']); }
      _page = items['meta']['current_page'];
      _hasMore = items['meta']['current_page'] < items['meta']['last_page'];
      _loading = false;
      _loadingMore = false;
    });
  }

  Future<void> _refresh() async {
    setState(() => _refreshing = true);
    await ref.read(userFeedsProvider.notifier).refreshFeed(widget.feedId);
    await _load(1);
    setState(() => _refreshing = false);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8F6F1),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        title: Text(widget.feedName,
            style: const TextStyle(color: Color(0xFF1A1A1A), fontWeight: FontWeight.w700)),
        actions: [
          _refreshing
              ? const Padding(
                  padding: EdgeInsets.all(14),
                  child: SizedBox(width: 20, height: 20,
                      child: CircularProgressIndicator(color: _red, strokeWidth: 2)))
              : IconButton(
                  icon: const Icon(Icons.refresh, color: Colors.black54),
                  onPressed: _refresh,
                ),
        ],
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: _red))
          : _articles.isEmpty
              ? const Center(
                  child: Text('Nessun articolo disponibile.',
                      style: TextStyle(color: Colors.black45)))
              : ListView.separated(
                  controller: _scrollCtrl,
                  itemCount: _articles.length + (_loadingMore ? 1 : 0),
                  separatorBuilder: (_, __) => const Divider(height: 1, color: Color(0xFFF3F4F6)),
                  itemBuilder: (ctx, i) {
                    if (i == _articles.length) {
                      return const Padding(
                        padding: EdgeInsets.all(16),
                        child: Center(child: CircularProgressIndicator(color: _red, strokeWidth: 2)),
                      );
                    }
                    final a = _articles[i];
                    return _ArticleTile(article: a);
                  },
                ),
    );
  }
}

class _ArticleTile extends StatelessWidget {
  final Map<String, dynamic> article;
  const _ArticleTile({required this.article});

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: () {
        // Apri URL nel browser
      },
      child: Container(
        color: Colors.white,
        padding: const EdgeInsets.all(12),
        child: Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            if (article['url_to_image'] != null) ...[
              ClipRect(
                child: Image.network(
                  article['url_to_image'],
                  width: 72, height: 60,
                  fit: BoxFit.cover,
                  errorBuilder: (_, __, ___) => const SizedBox(width: 72, height: 60),
                ),
              ),
              const SizedBox(width: 12),
            ],
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(article['title'] ?? '',
                      style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600, height: 1.3),
                      maxLines: 3, overflow: TextOverflow.ellipsis),
                  if (article['description'] != null) ...[
                    const SizedBox(height: 4),
                    Text(article['description'],
                        style: const TextStyle(fontSize: 12, color: Colors.black45),
                        maxLines: 2, overflow: TextOverflow.ellipsis),
                  ],
                  if (article['published_at'] != null) ...[
                    const SizedBox(height: 4),
                    Text(_formatDate(article['published_at']),
                        style: const TextStyle(fontSize: 11, color: Colors.black26)),
                  ],
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  String _formatDate(String? str) {
    if (str == null) return '';
    try {
      final d = DateTime.parse(str).toLocal();
      return '${d.day}/${d.month}/${d.year}';
    } catch (_) {
      return '';
    }
  }
}
