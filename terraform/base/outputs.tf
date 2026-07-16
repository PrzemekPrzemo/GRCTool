output "vpc_id" {
  description = "ID of the VPC"
  value       = module.vpc.vpc_id
}

output "vpc_public_subnets" {
  description = "List of public subnet IDs"
  value       = module.vpc.public_subnets
}

output "ecs_security_group_id" {
  description = "Security group ID for ECS Fargate Tasks"
  value       = aws_security_group.ecs.id
}

output "ecr_repository_url" {
  description = "URL of the ECR repository"
  value       = aws_ecr_repository.app.repository_url
}

output "rds_endpoint" {
  description = "Endpoint of the RDS MySQL instance (address:port)"
  value       = aws_db_instance.main.endpoint
}

output "rds_address" {
  description = "Hostname address of the RDS MySQL instance"
  value       = aws_db_instance.main.address
}

output "db_password_secret_arn" {
  description = "ARN of the DB password secret in AWS Secrets Manager"
  value       = aws_secretsmanager_secret.db_password.arn
}

output "app_key_secret_arn" {
  description = "ARN of the APP_KEY secret in AWS Secrets Manager"
  value       = aws_secretsmanager_secret.app_key.arn
}

output "s3_bucket_name" {
  description = "Name of the S3 storage bucket"
  value       = aws_s3_bucket.storage.id
}

output "ecs_execution_role_arn" {
  description = "ARN of ECS execution role"
  value       = aws_iam_role.ecs_execution_role.arn
}

output "ecs_task_role_arn" {
  description = "ARN of ECS task role"
  value       = aws_iam_role.ecs_task_role.arn
}
