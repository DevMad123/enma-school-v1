<?php

namespace App\Helpers;

class ActivityHelper
{
    public static function getActivityText($activity)
    {
        switch($activity->action) {
            case 'created':
                return "a créé un(e) {$activity->entity}";
            case 'updated':
                return "a mis à jour un(e) {$activity->entity}";
            case 'deleted':
                return "a supprimé un(e) {$activity->entity}";
            case 'viewed':
                return "a consulté un(e) {$activity->entity}";
            case 'submitted':
                return "a soumis un(e) {$activity->entity}";
            case 'downloaded':
                return "a téléchargé un(e) {$activity->entity}";
            case 'logged_in':
                return "s'est connecté(e)";
            case 'logged_out':
                return "s'est déconnecté(e)";
            default:
                return $activity->action . " " . $activity->entity;
        }
    }

    public static function getActionText($action)
    {
        switch($action) {
            case 'created':
                return 'a créé';
            case 'updated':
                return 'a mis à jour';
            case 'deleted':
                return 'a supprimé';
            case 'viewed':
                return 'a consulté';
            case 'submitted':
                return 'a soumis';
            case 'downloaded':
                return 'a téléchargé';
            case 'graded':
                return 'a noté';
            case 'corrected':
                return 'a corrigé';
            case 'published':
                return 'a publié';
            default:
                return $action;
        }
    }

    public static function getActivityColor($action)
    {
        switch($action) {
            case 'created':
            case 'published':
                return 'bg-success';
            case 'updated':
            case 'graded':
                return 'bg-info';
            case 'deleted':
                return 'bg-danger';
            case 'viewed':
            case 'downloaded':
                return 'bg-primary';
            case 'submitted':
                return 'bg-warning';
            default:
                return 'bg-secondary';
        }
    }

    public static function getEntityIcon($entity)
    {
        switch($entity) {
            case 'course':
                return '📚';
            case 'assignment':
                return '📝';
            case 'student':
                return '🎓';
            case 'teacher':
                return '🧑‍🏫';
            case 'payment':
                return '💰';
            case 'grade':
                return '📊';
            case 'evaluation':
                return '📋';
            default:
                return '📄';
        }
    }
}