import 'package:flutter/material.dart';
import 'package:flutter_riverpod/flutter_riverpod.dart';
import 'package:go_router/go_router.dart';
import 'package:shimmer/shimmer.dart';
import '../../core/providers/topics_provider.dart';

class CoverageScreen extends ConsumerStatefulWidget {
  const CoverageScreen({super.key});

  @override
  ConsumerState<CoverageScreen> createState() => _CoverageScreenState();
}

class _CoverageScreenState extends ConsumerState<CoverageScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      ref.read(topicsProvider.notifier).fetchTopics();
    });
  }

  @override
  Widget build(BuildContext context) {
    final state = ref.watch(topicsProvider);

    return Scaffold(
      backgroundColor: const Color(0xFFF8F6F1),
      appBar: AppBar(
        backgroundColor: Colors.white,
        elevation: 0,
        centerTitle: false,
        title: const Text(
          'Coverage Comparativa',
          style: TextStyle(color: Color(0xFF1A1A1A), fontSize: 18, fontWeight: FontWeight.w700),
        ),
      ),
      body: Builder(builder: (ctx) {
        if (state.loading) return _buildSkeleton();
        if (state.error != null) {
          return Center(child: Text(state.error!, style: const TextStyle(color: Colors.red)));
        }
        if (state.topics.isEmpty) {
          return const Center(child: Text('Nessun topic disponibile.\nTorna più tardi!', textAlign: TextAlign.center));
        }

        return ListView.separated(
          padding: const EdgeInsets.all(16),
          itemCount: state.topics.length,
          separatorBuilder: (_, __) => const SizedBox(height: 8),
          itemBuilder: (ctx, i) {
            final topic = state.topics[i];
            return GestureDetector(
              onTap: () => context.go('/coverage/${topic.id}'),
              child: Container(
                decoration: BoxDecoration(
                  color: Colors.white,
                  border: Border.all(color: Colors.grey.shade200),
                ),
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: Text(
                            topic.title,
                            style: const TextStyle(
                              fontSize: 15,
                              fontWeight: FontWeight.w700,
                              color: Color(0xFF1A1A1A),
                              height: 1.3,
                            ),
                          ),
                        ),
                        const Icon(Icons.chevron_right, color: Color(0xFFC41E3A)),
                      ],
                    ),
                    const SizedBox(height: 4),
                    Text(
                      '${topic.articleCount} articoli',
                      style: const TextStyle(fontSize: 12, color: Colors.grey),
                    ),
                    if (topic.keywords.isNotEmpty) ...[
                      const SizedBox(height: 8),
                      Wrap(
                        spacing: 4,
                        runSpacing: 4,
                        children: topic.keywords.take(4).map((kw) => Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                          decoration: BoxDecoration(
                            color: Colors.grey.shade100,
                            borderRadius: BorderRadius.circular(12),
                          ),
                          child: Text(kw, style: const TextStyle(fontSize: 11, color: Colors.black54)),
                        )).toList(),
                      ),
                    ],
                  ],
                ),
              ),
            );
          },
        );
      }),
    );
  }

  Widget _buildSkeleton() {
    return Shimmer.fromColors(
      baseColor: Colors.grey.shade200,
      highlightColor: Colors.grey.shade100,
      child: ListView.builder(
        padding: const EdgeInsets.all(16),
        itemCount: 8,
        itemBuilder: (_, __) => Container(
          margin: const EdgeInsets.only(bottom: 8),
          height: 80,
          decoration: BoxDecoration(color: Colors.white, border: Border.all(color: Colors.grey.shade200)),
        ),
      ),
    );
  }
}
