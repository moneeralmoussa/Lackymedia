<?php

namespace AuditBundle\Twig;

use DataDog\AuditBundle\Entity\AuditLog;

class AuditExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        $defaults = [
            'is_safe' => ['html'],
            'needs_environment' => true,
        ];

        return [
            new \Twig_SimpleFunction('audit', [$this, 'audit'], $defaults),
            new \Twig_SimpleFunction('audit_value', [$this, 'value'], $defaults),
            new \Twig_SimpleFunction('audit_assoc', [$this, 'assoc'], $defaults),
            new \Twig_SimpleFunction('audit_blame', [$this, 'blame'], $defaults),
            new \Twig_SimpleFunction('audit_rename', [$this, 'rename'], $defaults),
        ];
    }

    public function audit(\Twig_Environment $twig, AuditLog $log)
    {
        return $twig->render("AuditBundle:Audit:{$log->getAction()}.html.twig", compact('log'));
    }

    public function assoc(\Twig_Environment $twig, $assoc)
    {
        return $twig->render("AuditBundle:Audit:assoc.html.twig", compact('assoc'));
    }

    public function blame(\Twig_Environment $twig, $blame)
    {
        return $twig->render("AuditBundle:Audit:blame.html.twig", compact('blame'));
    }

    public function rename(\Twig_Environment $twig, $val)
    {
        $name ="";
        switch ($val) {
        case "Absence":
            $name = "Abwesenheit";
            break;
        case "Employee":
            $name = "Mitarbeiter";
            break;
        case "User":
            $name = "Benutzer";
            break;
        case "Contract":
            $name = "Vertrag";
            break;
        case "Countryspecificexpenses":
            $name = "Länderspezifische Spesensätze";
            break;
        case "Vacationlock":
            $name = "Urlaubssperre";
            break;
        case "Department":
            $name = "Abteilung";
            break;
        case "Expense":
            $name = "Spesen";
            break;
        case "Location":
            $name = "Standort";
            break;
        case "Reason":
            $name = "Grund";
            break;
        case "Vehicle":
            $name = "Fahrzeug";
            break;
        case "Workday":
            $name = "Arbeitstag";
            break;
        case "Workingtime":
            $name = "Arbeitszeit";
            break;
        case "Vehicle Log":
            $name = "Fahrtenbuch";
            break;
        case "Vehicle Log Position":
            $name = "Fahrzeug Standort";
            break;
        case "Absence Clearing":
            $name = "Abwesenheitskorrektur";
            break;
        case "Provenexpense":
            $name = "Spesenantrag";
            break;
        case "Absence Detail Clearing":
            $name = "Detailierte Abwesenheit";
            break;
      }
        return $name;
    }

    public function value(\Twig_Environment $twig, $val)
    {
        switch (true) {
        case is_bool($val):
            return $val ? 'true' : 'false';
        case is_array($val) && isset($val['fk']):
            return $this->assoc($twig, $val);
        case is_array($val):
            return json_encode($val);
        case is_string($val):
            return strlen($val) > 60 ? substr($val, 0, 60) . '...' : $val;
        case is_null($val):
            return 'NULL';
        default:
            return $val;
        }
    }

    public function getName()
    {
        return 'app_audit_extension';
    }
}
