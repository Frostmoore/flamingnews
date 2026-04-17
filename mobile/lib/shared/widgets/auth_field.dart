import 'package:flutter/material.dart';

class AuthField extends StatefulWidget {
  final TextEditingController controller;
  final String label;
  final bool obscure;
  final bool showToggle;
  final TextInputType type;
  final String? hint;

  const AuthField({
    super.key,
    required this.controller,
    required this.label,
    this.obscure = false,
    this.showToggle = false,
    this.type = TextInputType.text,
    this.hint,
  });

  @override
  State<AuthField> createState() => _AuthFieldState();
}

class _AuthFieldState extends State<AuthField> {
  late bool _obscured;

  @override
  void initState() {
    super.initState();
    _obscured = widget.obscure;
  }

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          widget.label,
          style: const TextStyle(fontSize: 12, fontWeight: FontWeight.w600, color: Colors.black54),
        ),
        const SizedBox(height: 4),
        TextField(
          controller: widget.controller,
          obscureText: _obscured,
          keyboardType: widget.type,
          decoration: InputDecoration(
            hintText: widget.hint,
            hintStyle: TextStyle(fontSize: 13, color: Colors.grey.shade400),
            contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
            border: const OutlineInputBorder(
              borderRadius: BorderRadius.zero,
              borderSide: BorderSide(color: Color(0xFFD1D5DB)),
            ),
            focusedBorder: const OutlineInputBorder(
              borderRadius: BorderRadius.zero,
              borderSide: BorderSide(color: Color(0xFFC41E3A)),
            ),
            filled: true,
            fillColor: Colors.white,
            suffixIcon: widget.showToggle
                ? IconButton(
                    icon: Icon(
                      _obscured ? Icons.visibility_outlined : Icons.visibility_off_outlined,
                      size: 18,
                      color: Colors.grey.shade500,
                    ),
                    onPressed: () => setState(() => _obscured = !_obscured),
                    splashRadius: 18,
                  )
                : null,
          ),
        ),
      ],
    );
  }
}
