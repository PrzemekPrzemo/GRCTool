locals {
  service_name = "grctool"

  # GitHub OIDC Role configuration
  github_role_name = "GitHubActions-GRC-SEC"
  github_org       = "TheSoftwareHouse"
  github_repos     = ["GRC-SEC"]
  github_role_policies = [
    "arn:aws:iam::aws:policy/AdministratorAccess"
  ]
}
