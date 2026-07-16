provider "aws" {
  region = "eu-west-1"

  default_tags {
    tags = {
      Project     = "GRC-Platform"
      ManagedBy   = "Terraform"
      owner       = "The Software House"
      Environment = "core"
    }
  }
}

terraform {
  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 5.53"
    }
    tls = {
      source  = "hashicorp/tls"
      version = "~> 4.0"
    }
  }

  # Uncomment after initial apply and run: terraform init -migrate-state
  backend "s3" {
    bucket       = "grctool-terraform-state"
    key          = "core/terraform.tfstate"
    region       = "eu-west-1"
    encrypt      = true
    kms_key_id   = "alias/grctool-terraform-state"
    use_lockfile = true
  }

  required_version = "~> 1.11"
}
