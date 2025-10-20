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

namespace Google\Service\DataManager;

class IngestAudienceMembersStatus extends \Google\Model
{
  protected $mobileDataIngestionStatusType = IngestMobileDataStatus::class;
  protected $mobileDataIngestionStatusDataType = '';
  protected $pairDataIngestionStatusType = IngestPairDataStatus::class;
  protected $pairDataIngestionStatusDataType = '';
  protected $userDataIngestionStatusType = IngestUserDataStatus::class;
  protected $userDataIngestionStatusDataType = '';

  /**
   * @param IngestMobileDataStatus
   */
  public function setMobileDataIngestionStatus(IngestMobileDataStatus $mobileDataIngestionStatus)
  {
    $this->mobileDataIngestionStatus = $mobileDataIngestionStatus;
  }
  /**
   * @return IngestMobileDataStatus
   */
  public function getMobileDataIngestionStatus()
  {
    return $this->mobileDataIngestionStatus;
  }
  /**
   * @param IngestPairDataStatus
   */
  public function setPairDataIngestionStatus(IngestPairDataStatus $pairDataIngestionStatus)
  {
    $this->pairDataIngestionStatus = $pairDataIngestionStatus;
  }
  /**
   * @return IngestPairDataStatus
   */
  public function getPairDataIngestionStatus()
  {
    return $this->pairDataIngestionStatus;
  }
  /**
   * @param IngestUserDataStatus
   */
  public function setUserDataIngestionStatus(IngestUserDataStatus $userDataIngestionStatus)
  {
    $this->userDataIngestionStatus = $userDataIngestionStatus;
  }
  /**
   * @return IngestUserDataStatus
   */
  public function getUserDataIngestionStatus()
  {
    return $this->userDataIngestionStatus;
  }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(IngestAudienceMembersStatus::class, 'Google_Service_DataManager_IngestAudienceMembersStatus');
