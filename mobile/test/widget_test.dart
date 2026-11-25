import 'package:material_ui/material_ui.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:umbulan/core/models/app_models.dart';
import 'package:umbulan/core/network/api_client.dart';
import 'package:umbulan/core/providers/auth_provider.dart';
import 'package:umbulan/core/services/push_notification_service.dart';
import 'package:umbulan/features/common/widgets/readiness_banner.dart';
import 'package:umbulan/main.dart';

void main() {
  test('UserModel parses 4-requirement completion status without phone field', () {
    final user = UserModel.fromJson({
      'id': 1,
      'name': 'Budi Operator',
      'email': 'budi@umbulan.co.id',
      'gender': 'Laki-laki',
      'level': 3,
      'schedule_type': 'normal',
      'roles': [
        {'role_name': 'Karyawan'},
      ],
      'has_face_descriptor': true,
      'has_signature': false,
      'is_approver': false,
      'is_account_complete': false,
      'completion_status': {
        'email_verified': true,
        'face_registered': true,
        'signature_set': false,
        'schedule_set': true,
        'is_complete': false,
      },
    });

    expect(user.email, 'budi@umbulan.co.id');
    expect(user.completionStatus.completedCount, 3);
    expect(user.completionStatus.progress, 0.75);
    expect(user.isAccountComplete, false);
  });

  testWidgets('Login screen and 4-item ReadinessBanner render cleanly', (tester) async {
    final apiClient = ApiClient();
    final auth = AuthProvider(
      apiClient: apiClient,
      pushService: PushNotificationService(),
    );

    // Override bootstrapping state off via fake UserModel check on ReadinessBanner
    await tester.pumpWidget(
      MaterialApp(
        home: Scaffold(
          body: SingleChildScrollView(
            child: ReadinessBanner(
              status: CompletionStatus(
                emailVerified: true,
                faceRegistered: false,
                signatureSet: false,
                scheduleSet: true,
                isComplete: false,
              ),
              onVerifyEmail: () {},
              onRegisterFace: () {},
              onSetupSignature: () {},
              onSetupSchedule: () {},
            ),
          ),
        ),
      ),
    );

    expect(find.textContaining('2/4 Syarat Selesai (50%)'), findsOneWidget);
    expect(find.text('Verifikasi Email'), findsOneWidget);
    expect(find.text('Biometrik Wajah'), findsOneWidget);
    expect(find.text('Tanda Tangan Digital'), findsOneWidget);
    expect(find.text('Jadwal Kerja'), findsOneWidget);
    expect(find.textContaining('WhatsApp'), findsNothing);

    await tester.pumpWidget(UmbulanApp(overrideAuthProvider: auth));
    expect(find.textContaining('Memuat'), findsOneWidget);
  });
}
