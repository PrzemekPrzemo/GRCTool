output "alb_dns_name" {
  description = "URL of the Web Express Service"
  value       = module.ecs_web_express.service_url
}

output "ecs_cluster_name" {
  description = "Name of the ECS Cluster"
  value       = module.ecs.cluster_name
}
