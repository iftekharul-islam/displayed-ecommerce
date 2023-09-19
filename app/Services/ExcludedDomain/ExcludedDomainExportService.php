<?php

namespace App\Services\ExcludedDomain;

use Carbon\Carbon;
use App\Models\ExcludedDomain;
use App\Constants\ShortUrlConstant;


class ExcludedDomainExportService
{

    public function query(
        $data
    ) {

        $sortByKey = $data['sortByKey'];
        $sortByOrder = $data['sortByOrder'];
        $campaignId = $data['campaignId'];
        $domain = $data['domain'];

        return ExcludedDomain::query()
            ->with(['campaign:id,name'])
            ->when($campaignId !== ShortUrlConstant::ALL, function ($query) use ($campaignId) {
                $query->where('campaign_id', $campaignId);
            })
            ->when($domain, function ($query, $domain) {
                $query->where('domain', 'LIKE', "%$domain%");
            })
            ->orderBy($sortByKey, $sortByOrder)
            ->lazyById(1000, 'id')
            ->map(
                function ($excludedDomain) {
                    return $this->map($excludedDomain);
                }
            )
            ->all();
    }


    public function map($excludedDomain): array
    {
        $createdAt = $excludedDomain->created_at ? Carbon::make($excludedDomain->created_at)->format('Y-m-d') : '-';
        $expiredAt = $excludedDomain->expired_at ? Carbon::make($excludedDomain->expired_at)->format('Y-m-d') : '-';

        return [
            'Campaign Name' => $excludedDomain->campaign->name ?? '-',
            'Domain' => $excludedDomain->domain,
            'Auto Renewal' => $excludedDomain->auto_renewal ? 'Yes' : 'No',
            'Status' => $this->getStatus((int) $excludedDomain->status, $excludedDomain->expired_at),
            'Note' => $excludedDomain->note ?? '-',
            'Expired On' => $expiredAt,
            'Created On' => $createdAt,
        ];
    }

    public function getStatus(int $status, $expiredAt): string
    {
        $currentDate = now()->format('Y-m-d');
        $expiredDate = Carbon::make($expiredAt)->format('Y-m-d');

        if ($expiredDate < $currentDate || $status === ShortUrlConstant::EXPIRED) {
            return 'Expired';
        } elseif ($expiredDate > $currentDate && $status === ShortUrlConstant::VALID) {
            return 'Valid';
        } else {
            return 'Invalid';
        }
    }

    public function itemsGenerator($items)
    {
        foreach ($items as $item) {
            yield $item;
        }
    }
}
