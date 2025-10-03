#!/bin/bash

echo "═══════════════════════════════════════════════════════════"
echo "  VISIONMETRICS - Verificações Locais de Qualidade"
echo "═══════════════════════════════════════════════════════════"
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Counter
ERRORS=0

echo "1️⃣  Verificando sintaxe PHP..."
if find backend -name "*.php" -exec php -l {} \; 2>&1 | grep -q "Parse error"; then
    echo -e "${RED}❌ Erros de sintaxe PHP encontrados${NC}"
    ERRORS=$((ERRORS + 1))
else
    echo -e "${GREEN}✅ Sintaxe PHP OK${NC}"
fi
echo ""

echo "2️⃣  Verificando Docker Compose..."
if docker-compose config > /dev/null 2>&1; then
    echo -e "${GREEN}✅ docker-compose.yml válido${NC}"
else
    echo -e "${RED}❌ docker-compose.yml tem erros${NC}"
    ERRORS=$((ERRORS + 1))
fi
echo ""

echo "3️⃣  Verificando migrations SQL..."
if [ -f "sql/schema.sql" ] && [ -f "sql/migrations/add_missing_tables.sql" ]; then
    echo -e "${GREEN}✅ Arquivos de migration encontrados${NC}"
else
    echo -e "${YELLOW}⚠️  Alguns arquivos de migration não encontrados${NC}"
fi
echo ""

echo "4️⃣  Verificando estrutura de diretórios..."
REQUIRED_DIRS=("backend" "frontend" "sql" "worker" "scripts" "uploads")
for dir in "${REQUIRED_DIRS[@]}"; do
    if [ -d "$dir" ]; then
        echo -e "${GREEN}✓${NC} $dir"
    else
        echo -e "${RED}✗${NC} $dir ${RED}(não encontrado)${NC}"
        ERRORS=$((ERRORS + 1))
    fi
done
echo ""

echo "5️⃣  Verificando variáveis de ambiente..."
if [ -f ".env" ]; then
    echo -e "${GREEN}✅ Arquivo .env encontrado${NC}"
else
    echo -e "${YELLOW}⚠️  Arquivo .env não encontrado (use env.example)${NC}"
fi
echo ""

echo "═══════════════════════════════════════════════════════════"
if [ $ERRORS -eq 0 ]; then
    echo -e "${GREEN}✅ TODAS AS VERIFICAÇÕES PASSARAM!${NC}"
    exit 0
else
    echo -e "${RED}❌ $ERRORS erros encontrados${NC}"
    exit 1
fi



