import 'package:flutter/material.dart';

// Global Key for accessing Navigator state without BuildContext
// Used for automatic redirection to login on session expiry
final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();
