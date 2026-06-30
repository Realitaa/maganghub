<?php

namespace App\Enums;

enum GroupTimelineType: string
{
    case SubmissionCreated = 'SUBMISSION_CREATED';
    case SubmissionRejected = 'SUBMISSION_REJECTED';
    case SubmissionApproved = 'SUBMISSION_APPROVED';
    case ApplicationLetterPrinted = 'APPLICATION_LETTER_PRINTED';
    case CompanyReplyUploaded = 'COMPANY_REPLY_UPLOADED';
    case AdministrationCompleted = 'ADMINISTRATION_COMPLETED';
    case CompanyRejected = 'COMPANY_REJECTED';
    case CompanyPartiallyAccepted = 'COMPANY_PARTIALLY_ACCEPTED';
}
