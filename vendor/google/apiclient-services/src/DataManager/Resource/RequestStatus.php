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

namespace Google\Service\DataManager\Resource;

use Google\Service\DataManager\RetrieveRequestStatusResponse;

/**
 * The "requestStatus" collection of methods.
 * Typical usage is:
 *  <code>
 *   $datamanagerService = new Google\Service\DataManager(...);
 *   $requestStatus = $datamanagerService->requestStatus;
 *  </code>
 */
class RequestStatus extends \Google\Service\Resource
{
  /**
   * Gets the status of a request given request id. (requestStatus.retrieve)
   *
   * @param array $optParams Optional parameters.
   *
   * @opt_param string requestId Required. Required. The request ID of the Data
   * Manager API request.
   * @return RetrieveRequestStatusResponse
   * @throws \Google\Service\Exception
   */
  public function retrieve($optParams = [])
  {
    $params = [];
    $params = array_merge($params, $optParams);
    return $this->call('retrieve', [$params], RetrieveRequestStatusResponse::class);
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(RequestStatus::class, 'Google_Service_DataManager_Resource_RequestStatus');
