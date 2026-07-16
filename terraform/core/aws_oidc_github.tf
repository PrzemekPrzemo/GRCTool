module "aws_oidc_github" {
  source = "git@github.com:TheSoftwareHouse/aws-oidc-github.git?ref=main"

  role_name     = local.github_role_name
  github_org    = local.github_org
  github_repos  = local.github_repos
  role_policies = local.github_role_policies
}
