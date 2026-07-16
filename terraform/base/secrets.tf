# Application Encryption Key Secret
resource "aws_secretsmanager_secret" "app_key" {
  name                    = "${var.app_name}-${var.environment}-app-key"
  recovery_window_in_days = 0

  tags = {
    Name = "${var.app_name}-${var.environment}-app-key"
  }
}

resource "aws_secretsmanager_secret_version" "app_key" {
  secret_id     = aws_secretsmanager_secret.app_key.id
  # Example variable - update with a valid laravel encryption key directly in AWS Console
  secret_string = "base64:4eXh5g/sK1qA2kO1V8X/l9fJmN3vC0uP6rW8yZ2xQ4E="

  lifecycle {
    ignore_changes = [secret_string]
  }
}
