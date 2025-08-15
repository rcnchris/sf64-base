.DEFAULT_GOAL = help
.PHONY: help assets

SUPPORTED_COMMANDS := command controller controller-crud controller-js entity fixtures form component listener
SUPPORTS_MAKE_ARGS := $(findstring $(firstword $(MAKECMDGOALS)), $(SUPPORTED_COMMANDS))
ifneq "$(SUPPORTS_MAKE_ARGS)" ""
  COMMAND_ARGS := $(wordlist 2,$(words $(MAKECMDGOALS)),$(MAKECMDGOALS))
  $(eval $(COMMAND_ARGS):;@:)
endif

# Constantes
DB_NAME = sf64-starter

DEPLOY_HOST = $(SAVE_IP)
DEPLOY_USER = $(SAVE_USER)
DEPLOY_DIR = /volume1/web/sf64-starter

TIME = $(shell date +'%Y-%m-%d-%H-%M')
PROJECT_DIR = $(shell pwd)
PROJECT_NAME = $(shell basename $(PROJECT_DIR))
SAVE_DIR = $(SAVE_ROOT_DIR)/$(PROJECT_NAME)
ARCHIVE_NAME = $(SAVE_DIR)_$(TIME).tar.gz

# Couleurs
RED = /bin/echo -e "\x1b[31m$1\x1b[0m"
GREEN = /bin/echo -e "\x1b[32m$1\x1b[0m"
ORANGE = /bin/echo -e "\x1b[33m$1\x1b[0m"
BLUE = /bin/echo -e "\x1b[34m$1\x1b[0m"

SFY = php bin/console

## —— 🔥 The Symfony 6.4 Project Base Makefile 🔥 ——————————————————————————
help: ## Afficher cette aide
	@clear
	@grep -E '(^[a-zA-Z0-9\./_-]+:.*?##.*$$)|(^##)' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}{printf "\033[32m%-30s\033[0m %s\n", $$1, $$2}' | sed -e 's/\[32m##/[33m/'

## —— 🎵 Symfony ——————————————————————————————————————————————————————————————
command: ## Créer une commande
	@$(SFY) make:command $(COMMAND_ARGS)

controller: ## Créer un controller
	@$(SFY) make:controller $(COMMAND_ARGS)

controller-admin: ## Créer un controller EasyAdmin
	@$(SFY) make:admin:crud

controller-crud: ## Créer un controller pour une entité
	@$(SFY) make:crud $(COMMAND_ARGS)

controller-js: ## Créer un controller javascript
	@$(SFY) make:stimulus-controller $(COMMAND_ARGS)

entity: ## Créer ou modifier une entité
	@$(SFY) make:entity $(COMMAND_ARGS)

fixtures: ## Créer une fixture
	@$(SFY) make:fixtures $(COMMAND_ARGS)

component: ## Créer un composant Twig
	@$(SFY) make:twig-component $(COMMAND_ARGS)

form: ## Créer un formulaire
	@$(SFY) make:form $(COMMAND_ARGS)

form-auto: ## Créer un champ auto complété
	@$(SFY) make:autocomplete-field

listener: ## Créer un listener/subscriber
	@$(SFY) make:listener $(COMMAND_ARGS)

## —— ✅ Tests ————————————————————————————————————————————————————————————————
test: ## Créer un test
	@$(SFY) make:test

sfy-tests: ## Tester la configuration Symfony
	@$(call ORANGE,"Tests de la configuration Symfony")
	@$(call BLUE,"Fichiers YAML")
	$(SFY) lint:yaml config translations --parse-tags
	@$(call BLUE,"Conteneur de dépendances")
	$(SFY) lint:container
	@$(call BLUE,"Templates Twig")
	$(SFY) lint:twig --show-deprecations templates/
	@$(call BLUE,"Mailer")
	$(SFY) mailer:test tst@tst.fr -vvv