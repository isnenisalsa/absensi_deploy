import 'package:dio/dio.dart';
void main() {
  final dio = Dio(BaseOptions(baseUrl: 'http://10.0.2.2:3000/api/'));
  // simulate Dio request
  var requestOptions = RequestOptions(path: 'attendance/today-status');
  var fullUrl = dio.options.baseUrl + requestOptions.path;
  print("Merged URL string: \$fullUrl");
}
