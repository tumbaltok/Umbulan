class StationModel {
  final int id;
  final String name;
  final String type;
  final String kodeStasiun;
  final double? latitude;
  final double? longitude;
  final int radiusMeters;

  StationModel({
    required this.id,
    required this.name,
    required this.type,
    required this.kodeStasiun,
    this.latitude,
    this.longitude,
    required this.radiusMeters,
  });

  factory StationModel.fromJson(Map<String, dynamic> json) {
    return StationModel(
      id: (json['id'] as num?)?.toInt() ?? 0,
      name: json['name']?.toString() ?? '-',
      type: json['type']?.toString() ?? 'stasiun',
      kodeStasiun: json['kode_stasiun']?.toString() ?? '',
      latitude: double.tryParse(json['latitude']?.toString() ?? ''),
      longitude: double.tryParse(json['longitude']?.toString() ?? ''),
      radiusMeters: (json['radius_meters'] as num?)?.toInt() ?? 1000,
    );
  }
}

class CompletionStatus {
  final bool emailVerified;
  final bool faceRegistered;
  final bool signatureSet;
  final bool scheduleSet;
  final bool isComplete;

  CompletionStatus({
    required this.emailVerified,
    required this.faceRegistered,
    required this.signatureSet,
    required this.scheduleSet,
    required this.isComplete,
  });

  int get completedCount =>
      (emailVerified ? 1 : 0) +
      (faceRegistered ? 1 : 0) +
      (signatureSet ? 1 : 0) +
      (scheduleSet ? 1 : 0);

  double get progress => completedCount / 4.0;

  factory CompletionStatus.fromJson(Map<String, dynamic> json) {
    return CompletionStatus(
      emailVerified: json['email_verified'] == true,
      faceRegistered: json['face_registered'] == true,
      signatureSet: json['signature_set'] == true,
      scheduleSet: json['schedule_set'] == true,
      isComplete: json['is_complete'] == true,
    );
  }
}

class UserModel {
  final int id;
  final String? nip;
  final String name;
  final String email;
  final String gender;
  final int? genderId;
  final int level;
  final String? scheduleType;
  final StationModel? station;
  final List<String> roleNames;
  final bool hasFaceDescriptor;
  final bool hasSignature;
  final bool isApprover;
  final bool isAccountComplete;
  final CompletionStatus completionStatus;

  UserModel({
    required this.id,
    this.nip,
    required this.name,
    required this.email,
    required this.gender,
    this.genderId,
    required this.level,
    this.scheduleType,
    this.station,
    required this.roleNames,
    required this.hasFaceDescriptor,
    required this.hasSignature,
    required this.isApprover,
    required this.isAccountComplete,
    required this.completionStatus,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    final rolesList = (json['roles'] as List?) ?? [];
    final roleNames = rolesList
        .map((e) => (e is Map ? e['role_name']?.toString() : null) ?? '')
        .where((s) => s.isNotEmpty)
        .toList();

    return UserModel(
      id: (json['id'] as num?)?.toInt() ?? 0,
      nip: json['nip']?.toString(),
      name: json['name']?.toString() ?? '',
      email: json['email']?.toString() ?? '',
      gender: json['gender']?.toString() ?? 'Laki-laki',
      genderId: (json['gender_id'] as num?)?.toInt(),
      level: (json['level'] as num?)?.toInt() ?? 3,
      scheduleType: json['schedule_type']?.toString(),
      station: json['station'] is Map<String, dynamic>
          ? StationModel.fromJson(json['station'] as Map<String, dynamic>)
          : null,
      roleNames: roleNames,
      hasFaceDescriptor: json['has_face_descriptor'] == true,
      hasSignature: json['has_signature'] == true,
      isApprover: json['is_approver'] == true,
      isAccountComplete: json['is_account_complete'] == true,
      completionStatus: json['completion_status'] is Map<String, dynamic>
          ? CompletionStatus.fromJson(json['completion_status'] as Map<String, dynamic>)
          : CompletionStatus(
              emailVerified: false,
              faceRegistered: false,
              signatureSet: false,
              scheduleSet: false,
              isComplete: false,
            ),
    );
  }
}

class JenisCutiModel {
  final int id;
  final String nameCuti;
  final bool wajibDokumen;
  final bool butuhSuratDokter;
  final List<SubCutiModel> subCutis;

  JenisCutiModel({
    required this.id,
    required this.nameCuti,
    required this.wajibDokumen,
    required this.butuhSuratDokter,
    required this.subCutis,
  });

  factory JenisCutiModel.fromJson(Map<String, dynamic> json) {
    final subs = (json['sub_cutis'] as List?) ?? [];
    return JenisCutiModel(
      id: (json['id'] as num?)?.toInt() ?? 0,
      nameCuti: json['name_cuti']?.toString() ?? '',
      wajibDokumen: json['apakah_wajib_dokumen'] == true,
      butuhSuratDokter: json['butuh_surat_dokter'] == true,
      subCutis: subs
          .whereType<Map<String, dynamic>>()
          .map(SubCutiModel.fromJson)
          .toList(),
    );
  }
}

class SubCutiModel {
  final int id;
  final int parentId;
  final String nameCuti;
  final int? durasiDefault;
  final bool wajibDokumen;

  SubCutiModel({
    required this.id,
    required this.parentId,
    required this.nameCuti,
    this.durasiDefault,
    required this.wajibDokumen,
  });

  factory SubCutiModel.fromJson(Map<String, dynamic> json) {
    return SubCutiModel(
      id: (json['id'] as num?)?.toInt() ?? 0,
      parentId: (json['parent_id'] as num?)?.toInt() ?? 0,
      nameCuti: json['name_cuti']?.toString() ?? '',
      durasiDefault: (json['durasi_default'] as num?)?.toInt(),
      wajibDokumen: json['apakah_wajib_dokumen'] == true,
    );
  }
}

class AttendanceRecord {
  final int id;
  final String date;
  final String? shiftType;
  final String? scheduledIn;
  final String? scheduledOut;
  final String? checkIn;
  final String? checkOut;
  final bool isLate;
  final bool isInRadiusCheckIn;
  final bool isOffDayWork;
  final bool isRestAfterOvertime;
  final String? checkoutDate;
  final String? checkoutType;
  final String? status;

  AttendanceRecord({
    required this.id,
    required this.date,
    this.shiftType,
    this.scheduledIn,
    this.scheduledOut,
    this.checkIn,
    this.checkOut,
    required this.isLate,
    required this.isInRadiusCheckIn,
    this.isOffDayWork = false,
    this.isRestAfterOvertime = false,
    this.checkoutDate,
    this.checkoutType,
    this.status,
  });

  factory AttendanceRecord.fromJson(Map<String, dynamic> json) {
    final rawDate = json['date']?.toString() ?? '';
    return AttendanceRecord(
      id: (json['id'] as num?)?.toInt() ?? 0,
      date: rawDate.length >= 10 ? rawDate.substring(0, 10) : rawDate,
      shiftType: json['shift_type']?.toString(),
      scheduledIn: json['scheduled_in']?.toString(),
      scheduledOut: json['scheduled_out']?.toString(),
      checkIn: json['check_in']?.toString(),
      checkOut: json['check_out']?.toString(),
      isLate: json['is_late'] == true,
      isInRadiusCheckIn: json['is_in_radius_check_in'] == true,
      isOffDayWork: json['is_off_day_work'] == true,
      isRestAfterOvertime: json['is_rest_after_overtime'] == true,
      checkoutDate: json['checkout_date']?.toString(),
      checkoutType: json['checkout_type']?.toString(),
      status: json['status']?.toString(),
    );
  }
}

class NotificationItemModel {
  final String id;
  final String title;
  final String body;
  final Map<String, dynamic> meta;
  final String? readAt;
  final String? createdAt;

  NotificationItemModel({
    required this.id,
    required this.title,
    required this.body,
    required this.meta,
    this.readAt,
    this.createdAt,
  });

  bool get isUnread => readAt == null || readAt!.isEmpty;

  factory NotificationItemModel.fromJson(Map<String, dynamic> json) {
    return NotificationItemModel(
      id: json['id']?.toString() ?? '',
      title: json['title']?.toString() ?? 'Notifikasi',
      body: json['body']?.toString() ?? '',
      meta: json['meta'] is Map
          ? Map<String, dynamic>.from(json['meta'] as Map)
          : <String, dynamic>{},
      readAt: json['read_at']?.toString(),
      createdAt: json['created_at']?.toString(),
    );
  }
}
