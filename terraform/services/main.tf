terraform {
  required_version = "~> 1.11"
  required_providers {
    aws = {
      source  = "hashicorp/aws"
      version = "~> 6.53.0"
    }
  }

  backend "s3" {
    bucket       = "grctool-terraform-state"
    key          = "services/terraform.tfstate"
    region       = "eu-west-1"
    encrypt      = true
    use_lockfile = true
  }
}

provider "aws" {
  region = var.aws_region

  default_tags {
    tags = {
      Project     = "GRC-Platform"
      Environment = var.environment
      ManagedBy   = "Terraform"
      Layer       = "services"
      owner       = "The Software House"
    }
  }
}

data "terraform_remote_state" "base" {
  backend = "s3"
  config = {
    bucket = "grctool-terraform-state"
    key    = "base/terraform.tfstate"
    region = "eu-west-1"
  }
}
