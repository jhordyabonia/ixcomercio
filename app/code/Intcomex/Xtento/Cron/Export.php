<?php

namespace Xtento\XtentoXtcore\Cron;

use Magento\Framework\Exception\LocalizedException;
use Xtento\ProductExport\Cron\Export as productsexport;

class Export extends productsexport
{
	public function execute($schedule)
    {
        try {
            if (!$this->moduleHelper->isModuleEnabled() || !$this->moduleHelper->isModuleProperlyInstalled()) {
                $this->xtentoLogger->info('Cronjob executed, but module is disabled or not installed properly. Stopping.');
                return true;
            }
            if (!$schedule) {
                $this->xtentoLogger->info('Cronjob executed, but no schedule is defined for cron. Stopping.');
                return true;
            }
            $jobCode = $schedule->getJobCode();
            preg_match('/profile_(\d+)/', $jobCode, $jobMatch);
            if (!isset($jobMatch[1])) {
                throw new LocalizedException(__('No profile ID found in job_code.'));
            }
            $profileId = $jobMatch[1];
            $profile = $this->profileFactory->create()->load($profileId);
            if (!$profile->getId()) {
                // Remove existing cronjobs
                $this->cronHelper->removeCronjobsLike('productexport_profile_' . $profileId . '_%', \Xtento\ProductExport\Cron\Export::CRON_GROUP);
                throw new LocalizedException(__('Profile ID %1 does not seem to exist anymore.', $profileId));
            }
            if (!$profile->getEnabled()) {
                return true; // Profile not enabled
            }
            if (!$profile->getCronjobEnabled()) {
                return true; // Cronjob not enabled
            }
            $exportModel = $this->exportFactory->create()->setProfile($profile);
            $filters = $this->addProfileFilters($profile);
            $exportModel->cronExport($filters);
        } catch (\Exception $e) {
            $this->xtentoLogger->critical('Cronjob exception for job_code ' . $jobCode . ': ' . $e->getMessage());
        }
        return true;
    }
}