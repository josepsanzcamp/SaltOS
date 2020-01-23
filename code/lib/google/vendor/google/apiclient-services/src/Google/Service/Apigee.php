<?php
/*
 * Copyright 2014 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */

/**
 * Service definition for Apigee (v1).
 *
 * <p>
 * The Apigee API lets you programmatically manage Apigee Hybrid with a set of
 * RESTful operations, including:  Create, edit, and delete API proxies  Manage
 * users  Deploy and undeploy proxy revisions  Configure environmentsFor
 * information on using the APIs described in this section, see Get started
 * using the APIs.</p>
 *
 * <p>
 * For more information about this service, see the API
 * <a href="https://cloud.google.com/apigee-api-management/" target="_blank">Documentation</a>
 * </p>
 *
 * @author Google, Inc.
 */
class Google_Service_Apigee extends Google_Service
{
  /** View and manage your data across Google Cloud Platform services. */
  const CLOUD_PLATFORM =
      "https://www.googleapis.com/auth/cloud-platform";

  public $hybrid_issuers;
  public $organizations;
  public $organizations_apiproducts;
  public $organizations_apiproducts_attributes;
  public $organizations_apis;
  public $organizations_apis_deployments;
  public $organizations_apis_keyvaluemaps;
  public $organizations_apis_revisions;
  public $organizations_apis_revisions_deployments;
  public $organizations_apps;
  public $organizations_companies;
  public $organizations_companies_apps;
  public $organizations_companies_apps_keys;
  public $organizations_deployments;
  public $organizations_developers;
  public $organizations_developers_apps;
  public $organizations_developers_apps_attributes;
  public $organizations_developers_apps_keys;
  public $organizations_developers_apps_keys_apiproducts;
  public $organizations_developers_apps_keys_create;
  public $organizations_developers_attributes;
  public $organizations_environments;
  public $organizations_environments_analytics_admin;
  public $organizations_environments_apis_deployments;
  public $organizations_environments_apis_revisions;
  public $organizations_environments_apis_revisions_debugsessions;
  public $organizations_environments_apis_revisions_debugsessions_data;
  public $organizations_environments_caches;
  public $organizations_environments_deployments;
  public $organizations_environments_flowhooks;
  public $organizations_environments_keystores;
  public $organizations_environments_keystores_aliases;
  public $organizations_environments_keyvaluemaps;
  public $organizations_environments_optimizedStats;
  public $organizations_environments_queries;
  public $organizations_environments_references;
  public $organizations_environments_resourcefiles;
  public $organizations_environments_sharedflows_deployments;
  public $organizations_environments_sharedflows_revisions;
  public $organizations_environments_stats;
  public $organizations_environments_targetservers;
  public $organizations_keyvaluemaps;
  public $organizations_operations;
  public $organizations_reports;
  public $organizations_sharedflows;
  public $organizations_sharedflows_deployments;
  public $organizations_sharedflows_revisions;
  public $organizations_sharedflows_revisions_deployments;
  
  /**
   * Constructs the internal representation of the Apigee service.
   *
   * @param Google_Client $client The client used to deliver requests.
   * @param string $rootUrl The root URL used for requests to the service.
   */
  public function __construct(Google_Client $client, $rootUrl = null)
  {
    parent::__construct($client);
    $this->rootUrl = $rootUrl ?: 'https://apigee.googleapis.com/';
    $this->servicePath = '';
    $this->batchPath = 'batch';
    $this->version = 'v1';
    $this->serviceName = 'apigee';

    $this->hybrid_issuers = new Google_Service_Apigee_Resource_HybridIssuers(
        $this,
        $this->serviceName,
        'issuers',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->organizations = new Google_Service_Apigee_Resource_Organizations(
        $this,
        $this->serviceName,
        'organizations',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'v1/organizations',
              'httpMethod' => 'POST',
              'parameters' => array(
                'parent' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'get' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'getSyncAuthorization' => array(
              'path' => 'v1/{+name}:getSyncAuthorization',
              'httpMethod' => 'POST',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'v1/{+parent}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'setSyncAuthorization' => array(
              'path' => 'v1/{+name}:setSyncAuthorization',
              'httpMethod' => 'POST',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'updateOrganization' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'POST',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_apiproducts = new Google_Service_Apigee_Resource_OrganizationsApiproducts(
        $this,
        $this->serviceName,
        'apiproducts',
        array(
          'methods' => array(
            'attributes' => array(
              'path' => 'v1/{+name}/attributes',
              'httpMethod' => 'POST',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'create' => array(
              'path' => 'v1/{+parent}/apiproducts',
              'httpMethod' => 'POST',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'query' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'entity' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'list' => array(
              'path' => 'v1/{+parent}/apiproducts',
              'httpMethod' => 'GET',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'attributename' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'count' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'startKey' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'expand' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'attributevalue' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'update' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_apiproducts_attributes = new Google_Service_Apigee_Resource_OrganizationsApiproductsAttributes(
        $this,
        $this->serviceName,
        'attributes',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'v1/{+parent}/attributes',
              'httpMethod' => 'GET',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'updateApiProductAttribute' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'POST',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_apis = new Google_Service_Apigee_Resource_OrganizationsApis(
        $this,
        $this->serviceName,
        'apis',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'v1/{+parent}/apis',
              'httpMethod' => 'POST',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'name' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'action' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'validate' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),'delete' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'v1/{+parent}/apis',
              'httpMethod' => 'GET',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'includeRevisions' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'includeMetaData' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_apis_deployments = new Google_Service_Apigee_Resource_OrganizationsApisDeployments(
        $this,
        $this->serviceName,
        'deployments',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'v1/{+parent}/deployments',
              'httpMethod' => 'GET',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_apis_keyvaluemaps = new Google_Service_Apigee_Resource_OrganizationsApisKeyvaluemaps(
        $this,
        $this->serviceName,
        'keyvaluemaps',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'v1/{+parent}/keyvaluemaps',
              'httpMethod' => 'POST',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'v1/{+parent}/keyvaluemaps',
              'httpMethod' => 'GET',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_apis_revisions = new Google_Service_Apigee_Resource_OrganizationsApisRevisions(
        $this,
        $this->serviceName,
        'revisions',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'format' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'list' => array(
              'path' => 'v1/{+parent}/revisions',
              'httpMethod' => 'GET',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'updateApiProxyRevision' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'POST',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'validate' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_apis_revisions_deployments = new Google_Service_Apigee_Resource_OrganizationsApisRevisionsDeployments(
        $this,
        $this->serviceName,
        'deployments',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'v1/{+parent}/deployments',
              'httpMethod' => 'GET',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_apps = new Google_Service_Apigee_Resource_OrganizationsApps(
        $this,
        $this->serviceName,
        'apps',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'v1/{+parent}/apps',
              'httpMethod' => 'GET',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'apptype' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'expand' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'keyStatus' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'ids' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'includeCred' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'rows' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'apiProduct' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'startKey' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'status' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_companies = new Google_Service_Apigee_Resource_OrganizationsCompanies(
        $this,
        $this->serviceName,
        'companies',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'v1/{+parent}/companies',
              'httpMethod' => 'POST',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'v1/{+parent}/companies',
              'httpMethod' => 'GET',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'includeDevelopers' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'startKey' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'count' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'expand' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),'update' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'action' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_companies_apps = new Google_Service_Apigee_Resource_OrganizationsCompaniesApps(
        $this,
        $this->serviceName,
        'apps',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'v1/{+parent}/apps',
              'httpMethod' => 'POST',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'v1/{+parent}/apps',
              'httpMethod' => 'GET',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'count' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'expand' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'startKey' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'update' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_companies_apps_keys = new Google_Service_Apigee_Resource_OrganizationsCompaniesAppsKeys(
        $this,
        $this->serviceName,
        'keys',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'updateCompanyAppKey' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'POST',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'action' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_deployments = new Google_Service_Apigee_Resource_OrganizationsDeployments(
        $this,
        $this->serviceName,
        'deployments',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'v1/{+parent}/deployments',
              'httpMethod' => 'GET',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'sharedFlows' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_developers = new Google_Service_Apigee_Resource_OrganizationsDevelopers(
        $this,
        $this->serviceName,
        'developers',
        array(
          'methods' => array(
            'attributes' => array(
              'path' => 'v1/{+parent}/attributes',
              'httpMethod' => 'POST',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'create' => array(
              'path' => 'v1/{+parent}/developers',
              'httpMethod' => 'POST',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'action' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'list' => array(
              'path' => 'v1/{+parent}/developers',
              'httpMethod' => 'GET',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'startKey' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'expand' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'ids' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'includeCompany' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'count' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'setDeveloperStatus' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'POST',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'action' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'update' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_developers_apps = new Google_Service_Apigee_Resource_OrganizationsDevelopersApps(
        $this,
        $this->serviceName,
        'apps',
        array(
          'methods' => array(
            'attributes' => array(
              'path' => 'v1/{+name}/attributes',
              'httpMethod' => 'POST',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'create' => array(
              'path' => 'v1/{+parent}/apps',
              'httpMethod' => 'POST',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'generateKeyPairOrUpdateDeveloperAppStatus' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'POST',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'action' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'get' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'query' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'entity' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'list' => array(
              'path' => 'v1/{+parent}/apps',
              'httpMethod' => 'GET',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'shallowExpand' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'startKey' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'count' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'expand' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),'update' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_developers_apps_attributes = new Google_Service_Apigee_Resource_OrganizationsDevelopersAppsAttributes(
        $this,
        $this->serviceName,
        'attributes',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'v1/{+parent}/attributes',
              'httpMethod' => 'GET',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'updateDeveloperAppAttribute' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'POST',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_developers_apps_keys = new Google_Service_Apigee_Resource_OrganizationsDevelopersAppsKeys(
        $this,
        $this->serviceName,
        'keys',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'v1/{+parent}/keys',
              'httpMethod' => 'POST',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'replaceDeveloperAppKey' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'updateDeveloperAppKey' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'POST',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'action' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_developers_apps_keys_apiproducts = new Google_Service_Apigee_Resource_OrganizationsDevelopersAppsKeysApiproducts(
        $this,
        $this->serviceName,
        'apiproducts',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'updateDeveloperAppKeyApiProduct' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'POST',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'action' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_developers_apps_keys_create = new Google_Service_Apigee_Resource_OrganizationsDevelopersAppsKeysCreate(
        $this,
        $this->serviceName,
        'create',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'v1/{+parent}/keys/create',
              'httpMethod' => 'POST',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_developers_attributes = new Google_Service_Apigee_Resource_OrganizationsDevelopersAttributes(
        $this,
        $this->serviceName,
        'attributes',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'v1/{+parent}/attributes',
              'httpMethod' => 'GET',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'updateDeveloperAttribute' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'POST',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_environments = new Google_Service_Apigee_Resource_OrganizationsEnvironments(
        $this,
        $this->serviceName,
        'environments',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'v1/{+parent}/environments',
              'httpMethod' => 'POST',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'name' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'delete' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'getDatalocation' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'repo' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'contentType' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'relativeFilePath' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'dataset' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'getDebugmask' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'getDeployedConfig' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'getIamPolicy' => array(
              'path' => 'v1/{+resource}:getIamPolicy',
              'httpMethod' => 'GET',
              'parameters' => array(
                'resource' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'options.requestedPolicyVersion' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
              ),
            ),'list' => array(
              'path' => 'v1/{+parent}/environments',
              'httpMethod' => 'GET',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'setIamPolicy' => array(
              'path' => 'v1/{+resource}:setIamPolicy',
              'httpMethod' => 'POST',
              'parameters' => array(
                'resource' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'subscribe' => array(
              'path' => 'v1/{+parent}:subscribe',
              'httpMethod' => 'POST',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'testIamPermissions' => array(
              'path' => 'v1/{+resource}:testIamPermissions',
              'httpMethod' => 'POST',
              'parameters' => array(
                'resource' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'unsubscribe' => array(
              'path' => 'v1/{+parent}:unsubscribe',
              'httpMethod' => 'POST',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'updateDebugmask' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'PATCH',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'updateMask' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'replaceRepeatedFields' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),'updateEnvironment' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'POST',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_environments_analytics_admin = new Google_Service_Apigee_Resource_OrganizationsEnvironmentsAnalyticsAdmin(
        $this,
        $this->serviceName,
        'admin',
        array(
          'methods' => array(
            'getSchemav2' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'type' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_environments_apis_deployments = new Google_Service_Apigee_Resource_OrganizationsEnvironmentsApisDeployments(
        $this,
        $this->serviceName,
        'deployments',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'v1/{+parent}/deployments',
              'httpMethod' => 'GET',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_environments_apis_revisions = new Google_Service_Apigee_Resource_OrganizationsEnvironmentsApisRevisions(
        $this,
        $this->serviceName,
        'revisions',
        array(
          'methods' => array(
            'deployments' => array(
              'path' => 'v1/{+name}/deployments',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'getDeployments' => array(
              'path' => 'v1/{+name}/deployments',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_environments_apis_revisions_debugsessions = new Google_Service_Apigee_Resource_OrganizationsEnvironmentsApisRevisionsDebugsessions(
        $this,
        $this->serviceName,
        'debugsessions',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'v1/{+parent}/debugsessions',
              'httpMethod' => 'POST',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'timeout' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'deleteData' => array(
              'path' => 'v1/{+name}/data',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'v1/{+parent}/debugsessions',
              'httpMethod' => 'GET',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_environments_apis_revisions_debugsessions_data = new Google_Service_Apigee_Resource_OrganizationsEnvironmentsApisRevisionsDebugsessionsData(
        $this,
        $this->serviceName,
        'data',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'v1/{+parent}/data',
              'httpMethod' => 'GET',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'limit' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_environments_caches = new Google_Service_Apigee_Resource_OrganizationsEnvironmentsCaches(
        $this,
        $this->serviceName,
        'caches',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'v1/{+parent}/caches',
              'httpMethod' => 'GET',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_environments_deployments = new Google_Service_Apigee_Resource_OrganizationsEnvironmentsDeployments(
        $this,
        $this->serviceName,
        'deployments',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'v1/{+parent}/deployments',
              'httpMethod' => 'GET',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'sharedFlows' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_environments_flowhooks = new Google_Service_Apigee_Resource_OrganizationsEnvironmentsFlowhooks(
        $this,
        $this->serviceName,
        'flowhooks',
        array(
          'methods' => array(
            'attachSharedFlowToFlowHook' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'detachSharedFlowFromFlowHook' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'v1/{+parent}/flowhooks',
              'httpMethod' => 'GET',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_environments_keystores = new Google_Service_Apigee_Resource_OrganizationsEnvironmentsKeystores(
        $this,
        $this->serviceName,
        'keystores',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'v1/{+parent}/keystores',
              'httpMethod' => 'POST',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'name' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'delete' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'v1/{+parent}/keystores',
              'httpMethod' => 'GET',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_environments_keystores_aliases = new Google_Service_Apigee_Resource_OrganizationsEnvironmentsKeystoresAliases(
        $this,
        $this->serviceName,
        'aliases',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'v1/{+parent}/aliases',
              'httpMethod' => 'POST',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'ignoreNewlineValidation' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'ignoreExpiryValidation' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'alias' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'format' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                '_password' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'csr' => array(
              'path' => 'v1/{+name}/csr',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'getCertificate' => array(
              'path' => 'v1/{+name}/certificate',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'v1/{+parent}/aliases',
              'httpMethod' => 'GET',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'ignoreExpiryValidation' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'ignoreNewlineValidation' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_environments_keyvaluemaps = new Google_Service_Apigee_Resource_OrganizationsEnvironmentsKeyvaluemaps(
        $this,
        $this->serviceName,
        'keyvaluemaps',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'v1/{+parent}/keyvaluemaps',
              'httpMethod' => 'POST',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'v1/{+parent}/keyvaluemaps',
              'httpMethod' => 'GET',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_environments_optimizedStats = new Google_Service_Apigee_Resource_OrganizationsEnvironmentsOptimizedStats(
        $this,
        $this->serviceName,
        'optimizedStats',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'sort' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'topk' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'tsAscending' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'timeRange' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'select' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'limit' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'offset' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'accuracy' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'sonar' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'realtime' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'tzo' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'sortby' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'filter' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'aggTable' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'timeUnit' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_environments_queries = new Google_Service_Apigee_Resource_OrganizationsEnvironmentsQueries(
        $this,
        $this->serviceName,
        'queries',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'v1/{+parent}/queries',
              'httpMethod' => 'POST',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'getResult' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'getResultView' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'v1/{+parent}/queries',
              'httpMethod' => 'GET',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'to' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'status' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'dataset' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'submittedBy' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'from' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'inclQueriesWithoutReport' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_environments_references = new Google_Service_Apigee_Resource_OrganizationsEnvironmentsReferences(
        $this,
        $this->serviceName,
        'references',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'v1/{+parent}/references',
              'httpMethod' => 'POST',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'v1/{+parent}/references',
              'httpMethod' => 'GET',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_environments_resourcefiles = new Google_Service_Apigee_Resource_OrganizationsEnvironmentsResourcefiles(
        $this,
        $this->serviceName,
        'resourcefiles',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'v1/{+parent}/resourcefiles',
              'httpMethod' => 'POST',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'type' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'name' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'delete' => array(
              'path' => 'v1/{+parent}/resourcefiles/{type}/{resourceFileId}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'type' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'resourceFileId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'v1/{+parent}/resourcefiles/{type}/{resourceFileId}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'type' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'resourceFileId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'v1/{+parent}/resourcefiles',
              'httpMethod' => 'GET',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'type' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'listEnvironmentResources' => array(
              'path' => 'v1/{+parent}/resourcefiles/{type}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'type' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'v1/{+parent}/resourcefiles/{type}/{resourceFileId}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'type' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'resourceFileId' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_environments_sharedflows_deployments = new Google_Service_Apigee_Resource_OrganizationsEnvironmentsSharedflowsDeployments(
        $this,
        $this->serviceName,
        'deployments',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'v1/{+parent}/deployments',
              'httpMethod' => 'GET',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_environments_sharedflows_revisions = new Google_Service_Apigee_Resource_OrganizationsEnvironmentsSharedflowsRevisions(
        $this,
        $this->serviceName,
        'revisions',
        array(
          'methods' => array(
            'deployments' => array(
              'path' => 'v1/{+name}/deployments',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'getDeployments' => array(
              'path' => 'v1/{+name}/deployments',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_environments_stats = new Google_Service_Apigee_Resource_OrganizationsEnvironmentsStats(
        $this,
        $this->serviceName,
        'stats',
        array(
          'methods' => array(
            'get' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'sort' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'topk' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'tsAscending' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'timeRange' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'select' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'limit' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'offset' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'accuracy' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'sonar' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'realtime' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'tzo' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'sortby' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'filter' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'timeUnit' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'aggTable' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_environments_targetservers = new Google_Service_Apigee_Resource_OrganizationsEnvironmentsTargetservers(
        $this,
        $this->serviceName,
        'targetservers',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'v1/{+parent}/targetservers',
              'httpMethod' => 'POST',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'name' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'delete' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'v1/{+parent}/targetservers',
              'httpMethod' => 'GET',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'update' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_keyvaluemaps = new Google_Service_Apigee_Resource_OrganizationsKeyvaluemaps(
        $this,
        $this->serviceName,
        'keyvaluemaps',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'v1/{+parent}/keyvaluemaps',
              'httpMethod' => 'POST',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'v1/{+parent}/keyvaluemaps',
              'httpMethod' => 'GET',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_operations = new Google_Service_Apigee_Resource_OrganizationsOperations(
        $this,
        $this->serviceName,
        'operations',
        array(
          'methods' => array(
            'cancel' => array(
              'path' => 'v1/{+name}:cancel',
              'httpMethod' => 'POST',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'v1/{+name}/operations',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'pageToken' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'pageSize' => array(
                  'location' => 'query',
                  'type' => 'integer',
                ),
                'filter' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_reports = new Google_Service_Apigee_Resource_OrganizationsReports(
        $this,
        $this->serviceName,
        'reports',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'v1/{+parent}/reports',
              'httpMethod' => 'POST',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'delete' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'v1/{+parent}/reports',
              'httpMethod' => 'GET',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'expand' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),'update' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'PUT',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_sharedflows = new Google_Service_Apigee_Resource_OrganizationsSharedflows(
        $this,
        $this->serviceName,
        'sharedflows',
        array(
          'methods' => array(
            'create' => array(
              'path' => 'v1/{+parent}/sharedflows',
              'httpMethod' => 'POST',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'action' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
                'name' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'delete' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'list' => array(
              'path' => 'v1/{+parent}/sharedflows',
              'httpMethod' => 'GET',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'includeRevisions' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
                'includeMetaData' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_sharedflows_deployments = new Google_Service_Apigee_Resource_OrganizationsSharedflowsDeployments(
        $this,
        $this->serviceName,
        'deployments',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'v1/{+parent}/deployments',
              'httpMethod' => 'GET',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_sharedflows_revisions = new Google_Service_Apigee_Resource_OrganizationsSharedflowsRevisions(
        $this,
        $this->serviceName,
        'revisions',
        array(
          'methods' => array(
            'delete' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'DELETE',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'get' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'GET',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'format' => array(
                  'location' => 'query',
                  'type' => 'string',
                ),
              ),
            ),'list' => array(
              'path' => 'v1/{+parent}/revisions',
              'httpMethod' => 'GET',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),'updateSharedFlowRevision' => array(
              'path' => 'v1/{+name}',
              'httpMethod' => 'POST',
              'parameters' => array(
                'name' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
                'validate' => array(
                  'location' => 'query',
                  'type' => 'boolean',
                ),
              ),
            ),
          )
        )
    );
    $this->organizations_sharedflows_revisions_deployments = new Google_Service_Apigee_Resource_OrganizationsSharedflowsRevisionsDeployments(
        $this,
        $this->serviceName,
        'deployments',
        array(
          'methods' => array(
            'list' => array(
              'path' => 'v1/{+parent}/deployments',
              'httpMethod' => 'GET',
              'parameters' => array(
                'parent' => array(
                  'location' => 'path',
                  'type' => 'string',
                  'required' => true,
                ),
              ),
            ),
          )
        )
    );
  }
}
