function base64_to_url_safe(base64) {
  return base64
    .replaceAll("+", ".")
    .replaceAll("/", "_")
    .replaceAll("=", "-");
}
