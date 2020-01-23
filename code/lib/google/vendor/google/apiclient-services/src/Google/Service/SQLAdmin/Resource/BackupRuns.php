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
 * The "backupRuns" collection of methods.
 * Typical usage is:
 *  <code>
 *   $sqlService = new Google_Service_SQLAdmin(...);
 *   $backupRuns = $sqlService->backupRuns;
 *  </code>
 */
class Google_Service_SQLAdmin_Resource_BackupRuns extends Google_Service_Resource
{
  /**
   * Deletes the backup taken by a backup run. (backupRuns.delete)
   *
   * @param string $project Project ID of the project that contains the instance.
   * @param string $instance Cloud SQL instance ID. This does not include the
   * project ID.
   * @param string $id The ID of the Backup Run to delete. To find a Backup Run
   * ID, use the list method.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string resourceName The name of the backupRun to delete. Format: p
   * rojects/{project}/locations/{location}/instances/{instance}/backupRuns/{backu
   * pRun}
   * @return Google_Service_SQLAdmin_Operation
   */
  public function delete($project, $instance, $id, $optParams = array())
  {
    $params = array('project' => $project, 'instance' => $instance, 'id' => $id);
    $params = array_merge($params, $optParams);
    return $this->call('delete', array($params), "Google_Service_SQLAdmin_Operation");
  }
  /**
   * Retrieves a resource containing information about a backup run.
   * (backupRuns.get)
   *
   * @param string $project Project ID of the project that contains the instance.
   * @param string $instance Cloud SQL instance ID. This does not include the
   * project ID.
   * @param string $id The ID of this Backup Run.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string resourceName Name of the resource backupRun. Format: projec
   * ts/{project}/locations/{location}/instances/{instance}/backupRuns/{backupRun}
   * @return Google_Service_SQLAdmin_BackupRun
   */
  public function get($project, $instance, $id, $optParams = array())
  {
    $params = array('project' => $project, 'instance' => $instance, 'id' => $id);
    $params = array_merge($params, $optParams);
    return $this->call('get', array($params), "Google_Service_SQLAdmin_BackupRun");
  }
  /**
   * Creates a new backup run on demand. This method is applicable only to Second
   * Generation instances. (backupRuns.insert)
   *
   * @param string $project Project ID of the project that contains the instance.
   * @param string $instance Cloud SQL instance ID. This does not include the
   * project ID.
   * @param Google_Service_SQLAdmin_BackupRun $postBody
   * @param array $optParams Optional parameters.
   *
   * @opt_param string parent The parent resource where Cloud SQL should create
   * this backupRun. Format:
   * projects/{project}/locations/{location}/instances/{instance}
   * @return Google_Service_SQLAdmin_Operation
   */
  public function insert($project, $instance, Google_Service_SQLAdmin_BackupRun $postBody, $optParams = array())
  {
    $params = array('project' => $project, 'instance' => $instance, 'postBody' => $postBody);
    $params = array_merge($params, $optParams);
    return $this->call('insert', array($params), "Google_Service_SQLAdmin_Operation");
  }
  /**
   * Lists all backup runs associated with a given instance and configuration in
   * the reverse chronological order of the backup initiation time.
   * (backupRuns.listBackupRuns)
   *
   * @param string $project Project ID of the project that contains the instance.
   * @param string $instance Cloud SQL instance ID. This does not include the
   * project ID.
   * @param array $optParams Optional parameters.
   *
   * @opt_param string parent The parent, which owns this collection of
   * backupRuns. Format:
   * projects/{project}/locations/{location}/instances/{instance}
   * @opt_param string pageToken A previously-returned page token representing
   * part of the larger set of results to view.
   * @opt_param int maxResults Maximum number of backup runs per response.
   * @return Google_Service_SQLAdmin_BackupRunsListResponse
   */
  public function listBackupRuns($project, $instance, $optParams = array())
  {
    $params = array('project' => $project, 'instance' => $instance);
    $params = array_merge($params, $optParams);
    return $this->call('list', array($params), "Google_Service_SQLAdmin_BackupRunsListResponse");
  }
}
