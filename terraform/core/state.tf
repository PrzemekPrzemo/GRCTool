module "terraform_state" {
  source = "git@github.com:TheSoftwareHouse/aws-terraform-state.git?ref=main"

  service_name = local.service_name
}
