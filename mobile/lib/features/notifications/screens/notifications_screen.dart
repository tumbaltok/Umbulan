import 'package:material_ui/material_ui.dart';
import 'package:provider/provider.dart';
import '../../../core/models/app_models.dart';
import '../../../core/network/api_client.dart';
import '../../../core/services/push_notification_service.dart';
import '../../../core/theme/app_theme.dart';

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
                style: const TextStyle(fontWeight: FontWeight.w700),
              ),
              const Spacer(),
              if (_unreadCount > 0)
                TextButton(
                  onPressed: _markAllRead,
                  child: const Text('Tandai Semua Dibaca'),
                ),
            ],
          ),
          if (_loading)
            const Padding(
              padding: EdgeInsets.all(40),
              child: Center(child: CircularProgressIndicator()),
            )
          else if (_items.isEmpty)
            const Padding(
              padding: EdgeInsets.all(48),
              child: Text(
                'Belum ada notifikasi masuk.',
                textAlign: TextAlign.center,
                style: TextStyle(color: AppTheme.textSecondary),
              ),
            )
          else
            ..._items.map(
              (n) => Card(
                margin: const EdgeInsets.only(bottom: 10),
                color: n.isUnread ? const Color(0xFFF0F9FF) : Colors.white,
                child: ListTile(
                  onTap: () => _markRead(n),
                  leading: Icon(
                    n.isUnread ? Icons.circle : Icons.check_circle_outline,
                    size: n.isUnread ? 14 : 20,
                    color: n.isUnread ? AppTheme.accent : AppTheme.textSecondary,
                  ),
                  title: Text(
                    n.title,
                    style: TextStyle(
                      fontWeight: n.isUnread ? FontWeight.w700 : FontWeight.w500,
                      fontSize: 14,
                    ),
                  ),
                  subtitle: Padding(
                    padding: const EdgeInsets.only(top: 4),
                    child: Text(n.body, style: const TextStyle(fontSize: 12.5)),
                  ),
                ),
              ),
            ),
        ],
      ),
    );
  }
}
