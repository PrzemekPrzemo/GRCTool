resource "aws_cloudwatch_log_group" "app" {
  name              = "/ecs/${var.app_name}-${var.environment}"
  retention_in_days = 30

  tags = {
    Name = "${var.app_name}-${var.environment}-logs"
  }
}

locals {
  app_environment = [
    { name = "APP_NAME", value = "GRC Platform" },
    { name = "APP_ENV", value = var.environment },
    { name = "APP_DEBUG", value = "false" },
    { name = "APP_URL", value = "https://${var.app_name}-${var.environment}-web.ecs.${var.aws_region}.on.aws" },
    { name = "LOG_CHANNEL", value = "stderr" },
    { name = "DB_CONNECTION", value = "mysql" },
    { name = "DB_HOST", value = data.terraform_remote_state.base.outputs.rds_address },
    { name = "DB_PORT", value = "3306" },
    { name = "DB_DATABASE", value = "grctool" },
    { name = "DB_USERNAME", value = "grctool" },
    { name = "SESSION_DRIVER", value = "database" },
    { name = "QUEUE_CONNECTION", value = "database" },
    { name = "CACHE_STORE", value = "database" },
    { name = "FILESYSTEM_DISK", value = "s3" },
    { name = "AWS_DEFAULT_REGION", value = var.aws_region },
    { name = "AWS_BUCKET", value = data.terraform_remote_state.base.outputs.s3_bucket_name },
    { name = "AWS_USE_PATH_STYLE_ENDPOINT", value = "false" }
  ]
}

# Official AWS ECS Cluster & Fixed Workers (queue, scheduler)
module "ecs" {
  source  = "terraform-aws-modules/ecs/aws"
  version = "~> 7.0"

  cluster_name = "${var.app_name}-${var.environment}-cluster"

  cluster_capacity_providers = ["FARGATE"]
  default_capacity_provider_strategy = {
    FARGATE = {
      weight = 100
    }
  }

  services = {
    queue = {
      cpu    = 256
      memory = 512

      desired_count = 1
      launch_type   = "FARGATE"

      create_task_exec_iam_role = false
      create_tasks_iam_role     = false
      create_security_group     = false
      task_exec_iam_role_arn    = data.terraform_remote_state.base.outputs.ecs_execution_role_arn
      tasks_iam_role_arn        = data.terraform_remote_state.base.outputs.ecs_task_role_arn

      subnet_ids         = data.terraform_remote_state.base.outputs.vpc_public_subnets
      security_group_ids = [data.terraform_remote_state.base.outputs.ecs_security_group_id]
      assign_public_ip   = true

      container_definitions = {
        queue = {
          essential   = true
          image       = "${data.terraform_remote_state.base.outputs.ecr_repository_url}:${var.image_tag}"
          command     = ["php", "artisan", "queue:work", "--sleep=3", "--tries=3"]
          environment = local.app_environment
          secrets = [
            {
              name      = "DB_PASSWORD"
              valueFrom = data.terraform_remote_state.base.outputs.db_password_secret_arn
            },
            {
              name      = "APP_KEY"
              valueFrom = data.terraform_remote_state.base.outputs.app_key_secret_arn
            }
          ]
          log_configuration = {
            logDriver = "awslogs"
            options = {
              "awslogs-group"         = aws_cloudwatch_log_group.app.name
              "awslogs-region"        = var.aws_region
              "awslogs-stream-prefix" = "queue"
            }
          }
        }
      }
    }

    scheduler = {
      cpu    = 256
      memory = 512

      desired_count = 1
      launch_type   = "FARGATE"

      create_task_exec_iam_role = false
      create_tasks_iam_role     = false
      create_security_group     = false
      task_exec_iam_role_arn    = data.terraform_remote_state.base.outputs.ecs_execution_role_arn
      tasks_iam_role_arn        = data.terraform_remote_state.base.outputs.ecs_task_role_arn

      subnet_ids         = data.terraform_remote_state.base.outputs.vpc_public_subnets
      security_group_ids = [data.terraform_remote_state.base.outputs.ecs_security_group_id]
      assign_public_ip   = true

      container_definitions = {
        scheduler = {
          essential   = true
          image       = "${data.terraform_remote_state.base.outputs.ecr_repository_url}:${var.image_tag}"
          command     = ["/bin/sh", "-c", "while true; do php artisan schedule:run; sleep 60; done"]
          environment = local.app_environment
          secrets = [
            {
              name      = "DB_PASSWORD"
              valueFrom = data.terraform_remote_state.base.outputs.db_password_secret_arn
            },
            {
              name      = "APP_KEY"
              valueFrom = data.terraform_remote_state.base.outputs.app_key_secret_arn
            }
          ]
          log_configuration = {
            logDriver = "awslogs"
            options = {
              "awslogs-group"         = aws_cloudwatch_log_group.app.name
              "awslogs-region"        = var.aws_region
              "awslogs-stream-prefix" = "scheduler"
            }
          }
        }
      }
    }

    migration = {
      cpu    = 256
      memory = 512

      desired_count = 0
      launch_type   = "FARGATE"

      create_task_exec_iam_role = false
      create_tasks_iam_role     = false
      create_security_group     = false
      task_exec_iam_role_arn    = data.terraform_remote_state.base.outputs.ecs_execution_role_arn
      tasks_iam_role_arn        = data.terraform_remote_state.base.outputs.ecs_task_role_arn

      subnet_ids         = data.terraform_remote_state.base.outputs.vpc_public_subnets
      security_group_ids = [data.terraform_remote_state.base.outputs.ecs_security_group_id]
      assign_public_ip   = true

      container_definitions = {
        migration = {
          essential   = true
          image       = "${data.terraform_remote_state.base.outputs.ecr_repository_url}:${var.image_tag}"
          command     = ["php", "artisan", "migrate", "--force"]
          environment = local.app_environment
          secrets = [
            {
              name      = "DB_PASSWORD"
              valueFrom = data.terraform_remote_state.base.outputs.db_password_secret_arn
            },
            {
              name      = "APP_KEY"
              valueFrom = data.terraform_remote_state.base.outputs.app_key_secret_arn
            }
          ]
          log_configuration = {
            logDriver = "awslogs"
            options = {
              "awslogs-group"         = aws_cloudwatch_log_group.app.name
              "awslogs-region"        = var.aws_region
              "awslogs-stream-prefix" = "migration"
            }
          }
        }
      }
    }
  }

  tags = {
    Name = "${var.app_name}-${var.environment}-cluster"
  }
}

# Web Service using Official Express Service Submodule (with CPU Autoscaling)
module "ecs_web_express" {
  source  = "terraform-aws-modules/ecs/aws//modules/express-service"
  version = "~> 7.0"

  name    = "${var.app_name}-${var.environment}-web"
  cluster = module.ecs.cluster_name

  cpu               = "512"
  memory            = "1024"
  health_check_path = "/up"

  network_configuration = {
    subnets         = data.terraform_remote_state.base.outputs.vpc_public_subnets
    security_groups = [data.terraform_remote_state.base.outputs.ecs_security_group_id]
  }

  create_security_group     = false
  create_execution_iam_role = false
  create_task_iam_role      = false
  execution_iam_role_arn    = data.terraform_remote_state.base.outputs.ecs_execution_role_arn
  task_iam_role_arn         = data.terraform_remote_state.base.outputs.ecs_task_role_arn

  primary_container = {
    image          = "${data.terraform_remote_state.base.outputs.ecr_repository_url}:${var.image_tag}"
    command        = ["/usr/bin/supervisord", "-c", "/etc/supervisord.conf"]
    container_port = 80
    environment    = local.app_environment
    secret = [
      {
        name       = "DB_PASSWORD"
        value_from = data.terraform_remote_state.base.outputs.db_password_secret_arn
      },
      {
        name       = "APP_KEY"
        value_from = data.terraform_remote_state.base.outputs.app_key_secret_arn
      }
    ]
    aws_logs_configuration = {
      log_group         = aws_cloudwatch_log_group.app.name
      log_stream_prefix = "web"
    }
  }

  scaling_target = {
    auto_scaling_metric       = "AVERAGE_CPU"
    auto_scaling_target_value = 80
    min_task_count            = 1
    max_task_count            = 3
  }

  tags = {
    Name = "${var.app_name}-${var.environment}-web"
  }
}
