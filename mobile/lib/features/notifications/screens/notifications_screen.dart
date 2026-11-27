import 'package:material_ui/material_ui.dart';
import 'package:provider/provider.dart';
import '../../../core/models/app_models.dart';
import '../../../core/network/api_client.dart';
import '../../../core/services/push_notification_service.dart';
import '../../../core/theme/app_theme.dart';
import '../../common/widgets/ui_kit.dart';

class NotificationsScreen extends StatefulWidget {
  final VoidCallback onUnreadChanged;
  const NotificationsScreen({super.key, required this.onUnreadChanged});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  bool _loading = true;
  int _unreadCount = 0;
  List<NotificationItemModel> _items = [];

  @override
  void initState() {
    super.initState();
    _loadNotifications();
  }

  Future<void> _loadNotifications() async {
    setState(() => _loading = true);
    try {
      final res = await context.read<ApiClient>().get('/notifications');
      final data = res['data'] as Map<String, dynamic>;
      setState(() {
        _unreadCount = (data['unread_count'] as num?)?.toInt() ?? 0;
        _items = ((data['items'] as List?) ?? [])
            .whereType<Map<String, dynamic>>()
            .map(NotificationItemModel.fromJson)
            .toList();
      });
    } catch (_) {
    } finally {
      if (mounted) setState(() => _loading = false);
    }
  }

  Future<void> _markAllRead() async {
    await context.read<ApiClient>().post('/notifications/read-all');
    widget.onUnreadChanged();
    await _loadNotifications();
  }

  Future<void> _markRead(NotificationItemModel item) async {
    if (!item.isUnread) return;
    await context.read<ApiClient>().post('/notifications/${item.id}/read');
    widget.onUnreadChanged();
    await _loadNotifications();
  }

  @override
  Widget build(BuildContext context) {
    final pushService = context.read<PushNotificationService>();

    return RefreshIndicator(
      onRefresh: _loadNotifications,
      child: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          Container(
            padding: const EdgeInsets.all(12),
            decoration: BoxDecoration(
              color: pushService.isFirebaseAvailable
                  ? AppTheme.success.withValues(alpha: 0.08)
                  : const Color(0xFFF1F5F9),
              borderRadius: BorderRadius.circular(12),
              border: Border.all(color: AppTheme.border),
            ),
            child: Row(
              children: [
                Icon(
                  pushService.isFirebaseAvailable
                      ? Icons.notifications_active_outlined
                      : Icons.notifications_none,
                  color: pushService.isFirebaseAvailable ? AppTheme.success : AppTheme.textSecondary,
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: Text(
                    pushService.isFirebaseAvailable
                        ? 'Push Notification FCM Aktif di Perangkat Ini'
                        : 'Mode Kotak Masuk Aplikasi (FCM aktif setelah file Firebase ditambahkan)',
                    style: const TextStyle(fontSize: 12.5, color: AppTheme.textSecondary),
                  ),
                ),
              ],
            ),
          ),
          const SizedBox(height: 12),
          Row(
            children: [
              Text(
                'Belum dibaca: $_unreadCount',
                style: const TextStyle(fontWeight: FontWeight.w800, fontSize: 15, color: AppTheme.textPrimary),
              ),
              const Spacer(),
              if (_unreadCount > 0)
                TextButton.icon(
                  onPressed: _markAllRead,
                  icon: const Icon(Icons.done_all_rounded, size: 17),
                  label: const Text('Tandai Semua Dibaca'),
                ),
            ],
          ),
          const SizedBox(height: 8),
          if (_loading)
            const Padding(
              padding: EdgeInsets.all(40),
              child: Center(child: CircularProgressIndicator()),
            )
          else if (_items.isEmpty)
            const Card(
              child: UmbulanEmptyState(
                icon: Icons.notifications_none_rounded,
                title: 'Belum Ada Notifikasi Masuk',
                subtitle: 'Pembaruan status Cuti, CAR, dan MPR Anda akan muncul secara otomatis di sini.',
              ),
            )
          else
            ..._items.map(
              (n) => Card(
                margin: const EdgeInsets.only(bottom: 10),
                color: n.isUnread ? const Color(0xFFF0F9FF) : Colors.white,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                  side: BorderSide(
                    color: n.isUnread ? const Color(0xFF7DD3FC) : AppTheme.border,
                  ),
                ),
                child: InkWell(
                  borderRadius: BorderRadius.circular(16),
                  onTap: () => _markRead(n),
                  child: Padding(
                    padding: const EdgeInsets.all(14),
                    child: Row(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Container(
                          width: 38,
                          height: 38,
                          decoration: BoxDecoration(
                            color: (n.isUnread ? AppTheme.accent : AppTheme.textSecondary)
                                .withValues(alpha: 0.12),
                            borderRadius: BorderRadius.circular(11),
                          ),
                          child: Icon(
                            n.isUnread ? Icons.notifications_active_rounded : Icons.notifications_none_rounded,
                            size: 19,
                            color: n.isUnread ? AppTheme.primary : AppTheme.textSecondary,
                          ),
                        ),
                        const SizedBox(width: 12),
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Row(
                                children: [
                                  Expanded(
                                    child: Text(
                                      n.title,
                                      style: TextStyle(
                                        fontWeight: n.isUnread ? FontWeight.w800 : FontWeight.w600,
                                        fontSize: 14,
                                        color: AppTheme.textPrimary,
                                      ),
                                    ),
                                  ),
                                  if (n.isUnread)
                                    Container(
                                      width: 8,
                                      height: 8,
                                      decoration: const BoxDecoration(
                                        color: AppTheme.accent,
                                        shape: BoxShape.circle,
                                      ),
                                    ),
                                ],
                              ),
                              const SizedBox(height: 4),
                              Text(
                                n.body,
                                style: const TextStyle(
                                  fontSize: 12.5,
                                  height: 1.4,
                                  color: AppTheme.textSecondary,
                                ),
                              ),
                            ],
                          ),
                        ),
                      ],
                    ),
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }
}
