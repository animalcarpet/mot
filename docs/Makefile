# Makefile for MOT documentation, generated from sources using Sami.

SAMI_BIN           = vendor/bin/sami.php
ROOT_DIR           = .
API_SAMI_CFG       = api/sami.php
API_BUILD_DIR      = api/build
API_CACHE_DIR      = api/cache
FRONTEND_SAMI_CFG  = frontend/sami.php
FRONTEND_BUILD_DIR = frontend/build
FRONTEND_CACHE_DIR = frontend/cache

.DEFAULT_GOAL := all
.PHONY: help clean api frontend all

help:
	@echo "Please use \`make <target>' where <target> is one of:"
	@echo "  api       to build the MOT API documentation"
	@echo "  frontend  to build the MOT Frontend documentation"
	@echo "  clean     to remove files in the build and cache directories"

clean:
	-rm -rf $(API_BUILD_DIR)/*
	-rm -rf $(API_CACHE_DIR)/*
	-rm -rf $(FRONTEND_BUILD_DIR)/*
	-rm -rf $(FRONTEND_CACHE_DIR)/*

api:
	- php $(SAMI_BIN) update ${API_SAMI_CFG} -v
	@echo
	@echo "Build finished. The HTML pages are in $(API_BUILD_DIR)/"

frontend:
	- php $(SAMI_BIN) update ${FRONTEND_SAMI_CFG} -v
	@echo
	@echo "Build finished. The HTML pages are in $(FRONTEND_BUILD_DIR)/"

all: api frontend
