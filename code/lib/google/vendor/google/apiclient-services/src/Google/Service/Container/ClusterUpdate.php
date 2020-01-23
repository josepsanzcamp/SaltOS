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

class Google_Service_Container_ClusterUpdate extends Google_Collection
{
  protected $collection_key = 'desiredLocations';
  protected $desiredAddonsConfigType = 'Google_Service_Container_AddonsConfig';
  protected $desiredAddonsConfigDataType = '';
  protected $desiredBinaryAuthorizationType = 'Google_Service_Container_BinaryAuthorization';
  protected $desiredBinaryAuthorizationDataType = '';
  protected $desiredClusterAutoscalingType = 'Google_Service_Container_ClusterAutoscaling';
  protected $desiredClusterAutoscalingDataType = '';
  protected $desiredDatabaseEncryptionType = 'Google_Service_Container_DatabaseEncryption';
  protected $desiredDatabaseEncryptionDataType = '';
  public $desiredImageType;
  protected $desiredIntraNodeVisibilityConfigType = 'Google_Service_Container_IntraNodeVisibilityConfig';
  protected $desiredIntraNodeVisibilityConfigDataType = '';
  public $desiredLocations;
  public $desiredLoggingService;
  protected $desiredMasterAuthorizedNetworksConfigType = 'Google_Service_Container_MasterAuthorizedNetworksConfig';
  protected $desiredMasterAuthorizedNetworksConfigDataType = '';
  public $desiredMasterVersion;
  public $desiredMonitoringService;
  protected $desiredNodePoolAutoscalingType = 'Google_Service_Container_NodePoolAutoscaling';
  protected $desiredNodePoolAutoscalingDataType = '';
  public $desiredNodePoolId;
  public $desiredNodeVersion;
  protected $desiredResourceUsageExportConfigType = 'Google_Service_Container_ResourceUsageExportConfig';
  protected $desiredResourceUsageExportConfigDataType = '';
  protected $desiredVerticalPodAutoscalingType = 'Google_Service_Container_VerticalPodAutoscaling';
  protected $desiredVerticalPodAutoscalingDataType = '';

  /**
   * @param Google_Service_Container_AddonsConfig
   */
  public function setDesiredAddonsConfig(Google_Service_Container_AddonsConfig $desiredAddonsConfig)
  {
    $this->desiredAddonsConfig = $desiredAddonsConfig;
  }
  /**
   * @return Google_Service_Container_AddonsConfig
   */
  public function getDesiredAddonsConfig()
  {
    return $this->desiredAddonsConfig;
  }
  /**
   * @param Google_Service_Container_BinaryAuthorization
   */
  public function setDesiredBinaryAuthorization(Google_Service_Container_BinaryAuthorization $desiredBinaryAuthorization)
  {
    $this->desiredBinaryAuthorization = $desiredBinaryAuthorization;
  }
  /**
   * @return Google_Service_Container_BinaryAuthorization
   */
  public function getDesiredBinaryAuthorization()
  {
    return $this->desiredBinaryAuthorization;
  }
  /**
   * @param Google_Service_Container_ClusterAutoscaling
   */
  public function setDesiredClusterAutoscaling(Google_Service_Container_ClusterAutoscaling $desiredClusterAutoscaling)
  {
    $this->desiredClusterAutoscaling = $desiredClusterAutoscaling;
  }
  /**
   * @return Google_Service_Container_ClusterAutoscaling
   */
  public function getDesiredClusterAutoscaling()
  {
    return $this->desiredClusterAutoscaling;
  }
  /**
   * @param Google_Service_Container_DatabaseEncryption
   */
  public function setDesiredDatabaseEncryption(Google_Service_Container_DatabaseEncryption $desiredDatabaseEncryption)
  {
    $this->desiredDatabaseEncryption = $desiredDatabaseEncryption;
  }
  /**
   * @return Google_Service_Container_DatabaseEncryption
   */
  public function getDesiredDatabaseEncryption()
  {
    return $this->desiredDatabaseEncryption;
  }
  public function setDesiredImageType($desiredImageType)
  {
    $this->desiredImageType = $desiredImageType;
  }
  public function getDesiredImageType()
  {
    return $this->desiredImageType;
  }
  /**
   * @param Google_Service_Container_IntraNodeVisibilityConfig
   */
  public function setDesiredIntraNodeVisibilityConfig(Google_Service_Container_IntraNodeVisibilityConfig $desiredIntraNodeVisibilityConfig)
  {
    $this->desiredIntraNodeVisibilityConfig = $desiredIntraNodeVisibilityConfig;
  }
  /**
   * @return Google_Service_Container_IntraNodeVisibilityConfig
   */
  public function getDesiredIntraNodeVisibilityConfig()
  {
    return $this->desiredIntraNodeVisibilityConfig;
  }
  public function setDesiredLocations($desiredLocations)
  {
    $this->desiredLocations = $desiredLocations;
  }
  public function getDesiredLocations()
  {
    return $this->desiredLocations;
  }
  public function setDesiredLoggingService($desiredLoggingService)
  {
    $this->desiredLoggingService = $desiredLoggingService;
  }
  public function getDesiredLoggingService()
  {
    return $this->desiredLoggingService;
  }
  /**
   * @param Google_Service_Container_MasterAuthorizedNetworksConfig
   */
  public function setDesiredMasterAuthorizedNetworksConfig(Google_Service_Container_MasterAuthorizedNetworksConfig $desiredMasterAuthorizedNetworksConfig)
  {
    $this->desiredMasterAuthorizedNetworksConfig = $desiredMasterAuthorizedNetworksConfig;
  }
  /**
   * @return Google_Service_Container_MasterAuthorizedNetworksConfig
   */
  public function getDesiredMasterAuthorizedNetworksConfig()
  {
    return $this->desiredMasterAuthorizedNetworksConfig;
  }
  public function setDesiredMasterVersion($desiredMasterVersion)
  {
    $this->desiredMasterVersion = $desiredMasterVersion;
  }
  public function getDesiredMasterVersion()
  {
    return $this->desiredMasterVersion;
  }
  public function setDesiredMonitoringService($desiredMonitoringService)
  {
    $this->desiredMonitoringService = $desiredMonitoringService;
  }
  public function getDesiredMonitoringService()
  {
    return $this->desiredMonitoringService;
  }
  /**
   * @param Google_Service_Container_NodePoolAutoscaling
   */
  public function setDesiredNodePoolAutoscaling(Google_Service_Container_NodePoolAutoscaling $desiredNodePoolAutoscaling)
  {
    $this->desiredNodePoolAutoscaling = $desiredNodePoolAutoscaling;
  }
  /**
   * @return Google_Service_Container_NodePoolAutoscaling
   */
  public function getDesiredNodePoolAutoscaling()
  {
    return $this->desiredNodePoolAutoscaling;
  }
  public function setDesiredNodePoolId($desiredNodePoolId)
  {
    $this->desiredNodePoolId = $desiredNodePoolId;
  }
  public function getDesiredNodePoolId()
  {
    return $this->desiredNodePoolId;
  }
  public function setDesiredNodeVersion($desiredNodeVersion)
  {
    $this->desiredNodeVersion = $desiredNodeVersion;
  }
  public function getDesiredNodeVersion()
  {
    return $this->desiredNodeVersion;
  }
  /**
   * @param Google_Service_Container_ResourceUsageExportConfig
   */
  public function setDesiredResourceUsageExportConfig(Google_Service_Container_ResourceUsageExportConfig $desiredResourceUsageExportConfig)
  {
    $this->desiredResourceUsageExportConfig = $desiredResourceUsageExportConfig;
  }
  /**
   * @return Google_Service_Container_ResourceUsageExportConfig
   */
  public function getDesiredResourceUsageExportConfig()
  {
    return $this->desiredResourceUsageExportConfig;
  }
  /**
   * @param Google_Service_Container_VerticalPodAutoscaling
   */
  public function setDesiredVerticalPodAutoscaling(Google_Service_Container_VerticalPodAutoscaling $desiredVerticalPodAutoscaling)
  {
    $this->desiredVerticalPodAutoscaling = $desiredVerticalPodAutoscaling;
  }
  /**
   * @return Google_Service_Container_VerticalPodAutoscaling
   */
  public function getDesiredVerticalPodAutoscaling()
  {
    return $this->desiredVerticalPodAutoscaling;
  }
}
