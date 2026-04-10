import 'package:flutter/material.dart';
import 'dart:ui';
import 'custom_header.dart';

class GlassmorphicLayout extends StatelessWidget {
  final Widget child;
  final String title;
  final bool showBackButton;
  final VoidCallback? onBackPressed;
  final Widget? bottomAction; // E.g., CustomBottomNav or a Submit Button
  final double headerHeight;
  final double footerHeight;
  final String? photoUrl;

  const GlassmorphicLayout({
    super.key,
    required this.child,
    required this.title,
    this.showBackButton = false,
    this.onBackPressed,
    this.bottomAction,
    this.headerHeight = 120,
    this.footerHeight = 90,
    this.photoUrl,
  });

  @override
  Widget build(BuildContext context) {
    return Stack(
      children: [
        // 1. MAIN CONTENT (LAYER BAWAH)
        Positioned.fill(child: child),

        // 2. FIXED HEADER (LAYER ATAS)
        Positioned(
          top: 0,
          left: 0,
          right: 0,
          child: _buildHeader(context),
        ),

        // 3. FIXED FOOTER (LAYER ATAS)
        if (bottomAction != null)
          Positioned(
            bottom: 0,
            left: 0,
            right: 0,
            child: _buildFooter(context),
          ),
      ],
    );
  }

  Widget _buildHeader(BuildContext context) {
    return Stack(
      children: [
        // A. Background (Fading Blur)
        ShaderMask(
          shaderCallback: (rect) {
            return const LinearGradient(
              begin: Alignment.topCenter,
              end: Alignment.bottomCenter,
              stops: [0.6, 1.0],
              colors: [Colors.black, Colors.transparent],
            ).createShader(Rect.fromLTRB(0, 0, rect.width, rect.height));
          },
          blendMode: BlendMode.dstIn,
          child: ClipRect(
            child: BackdropFilter(
              filter: ImageFilter.blur(sigmaX: 10, sigmaY: 10),
              child: Container(
                height: headerHeight,
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.7),
                ),
              ),
            ),
          ),
        ),
        // B. Foreground (Solid UI)
        SafeArea(
          bottom: false,
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 20.0, vertical: 10.0),
            child: CustomHeader(
              title: title,
              showBackButton: showBackButton,
              onBackPressed: onBackPressed,
              photoUrl: photoUrl,
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildFooter(BuildContext context) {
    return Stack(
      alignment: Alignment.bottomCenter,
      children: [
        // A. Background (Fading Blur)
        ShaderMask(
          shaderCallback: (rect) {
            return const LinearGradient(
              begin: Alignment.bottomCenter,
              end: Alignment.topCenter,
              stops: [0.6, 1.0],
              colors: [Colors.black, Colors.transparent],
            ).createShader(Rect.fromLTRB(0, 0, rect.width, rect.height));
          },
          blendMode: BlendMode.dstIn,
          child: ClipRect(
            child: BackdropFilter(
              filter: ImageFilter.blur(sigmaX: 10, sigmaY: 10),
              child: Container(
                height: footerHeight,
                decoration: BoxDecoration(
                  color: Colors.white.withValues(alpha: 0.7),
                ),
              ),
            ),
          ),
        ),
        // B. Foreground (Solid UI)
        if (bottomAction != null) bottomAction!,
      ],
    );
  }
}
