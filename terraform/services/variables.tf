variable "aws_region" {
  description = "AWS region to deploy resources"
  type        = string
  default     = "eu-west-1"
}

variable "environment" {
  description = "Environment name (prod, staging, dev)"
  type        = string
  default     = "prod"
}

variable "app_name" {
  description = "Application name used for naming resources"
  type        = string
  default     = "grctool"
}

variable "image_tag" {
  description = "Docker image tag to deploy to ECS containers"
  type        = string
  default     = "latest"
}


