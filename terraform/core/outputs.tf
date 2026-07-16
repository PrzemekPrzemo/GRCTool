output "s3_bucket_name" {
  value       = module.terraform_state.s3_bucket_name
  description = "Name of S3 Bucket for Terraform State"
}

output "kms_alias" {
  value       = module.terraform_state.kms_alias
  description = "KMS Alias for Custom KMS Key"
}

output "github_role_arn" {
  value       = try(module.aws_oidc_github.role_arn, null)
  description = "GitHub Role ARN for OIDC authentication"
}
