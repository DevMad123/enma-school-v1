<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BusinessRuleException extends Exception
{
    /**
     * Le code de l'erreur métier
     */
    protected $businessCode;
    
    /**
     * Données additionnelles pour le contexte
     */
    protected $context;

    public function __construct(string $message = "", int $code = 0, ?Exception $previous = null, ?string $businessCode = null, array $context = [])
    {
        parent::__construct($message, $code, $previous);
        $this->businessCode = $businessCode;
        $this->context = $context;
    }

    /**
     * Rapporter l'exception
     */
    public function report(): void
    {
        // Log de l'erreur métier pour analyse
        \Log::warning('Règle métier violée', [
            'message' => $this->getMessage(),
            'business_code' => $this->businessCode,
            'context' => $this->context,
            'user_id' => auth()->id(),
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }

    /**
     * Rendre la réponse HTTP pour l'exception
     */
    public function render(Request $request): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $this->getMessage(),
            'type' => 'business_rule_violation'
        ];
        
        if ($this->businessCode) {
            $response['business_code'] = $this->businessCode;
        }
        
        if (!empty($this->context) && config('app.debug')) {
            $response['context'] = $this->context;
        }
        
        return response()->json($response, 422);
    }
    
    /**
     * Définir le code métier
     */
    public function setBusinessCode(string $code): self
    {
        $this->businessCode = $code;
        return $this;
    }
    
    /**
     * Ajouter du contexte
     */
    public function withContext(array $context): self
    {
        $this->context = array_merge($this->context, $context);
        return $this;
    }
    
    /**
     * Obtenir le code métier
     */
    public function getBusinessCode(): ?string
    {
        return $this->businessCode;
    }
    
    /**
     * Obtenir le contexte
     */
    public function getContext(): array
    {
        return $this->context;
    }
}