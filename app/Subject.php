<?php
/**
 * Created by PhpStorm.
 * User: unual
 * Date: 2019/3/15
 * Time: 11:36
 */

namespace App;

class Subject
{
    //库存现金
    const cashInStock = 1;
    //银行存款
    const bankDeposit = 2;
    //其他货币资金
    const otherCurrencyFunds = 3;
    //交易性金融资产
    const transactionalFinancialAssets = 10;
    //应收票据
    const notesReceivable = 11;
    //应收账款
    const accountsReceivable = 14;
    //预付账款
    const advancePayment = 15;
    //应收股利
    const dividendReceivable = 16;
    //应收利息
    const interestReceivable = 17;
    //其他应收款
    const otherReceivables = 18;
    //坏账准备
    const badDebtPreparation = 19;
    //应收账款坏账准备
    const allowanceForBadDebtsInAccountsReceivable = 20;
    //预付账款坏账准备
    const badDebtReserveForAdvanceAccounts = 21;
    //其他应收款坏账准备
    const allowanceForBadDebtsInOtherReceivables = 24;
    //材料采购
    const materialPurchase = 27;
    //在途物资
    const materialsInTransit = 28;
    //原材料
    const rawMaterial = 29;
    //材料成本差异
    const materialCostDifference = 30;
    //库存商品
    const merchandiseInStock = 31;
    //商品进销差价
    const differencesBetweenPurchasingAndSellingPrice = 33;
    //委托加工物资
    const entrustedProcessingMaterials = 34;
    //周转材料
    const workingCapitalConstructionMaterials = 35;
    //消耗性生物资产
    const consumableBiologicalAssets = 36;
    //存货跌价准备
    const inventoryFallingPriceReserves = 37;
    //持有至到期投资
    const holdingUpToMaturityInvestment = 38;
    //持有至到期投资减值准备
    const preparednessForImpairmentOfInvestmentHoldingsToMaturity = 39;
    //可供出售金融资产
    const sellableFinancialAssets = 40;
    //长期股权投资
    const longTermEquityInvestment = 41;
    //长期股权投资减值准备
    const preparednessForImpairmentOfLongTermEquityInvestment = 42;
    //投资性房地产
    const investmentRealEstate = 43;
    //投资性房地产累计折旧
    const accumulatedDepreciationOfInvestmentRealEstate = 44;
    //投资性房地产减值准备
    const reserveForImpairmentOfInvestmentRealEstate = 46;
    //长期应收款
    const longTermReceivables = 47;
    //未实现融资收益
    const unrealizedFinancingGains = 48;
    //固定资产
    const fixedAssets = 49;
    //累计折旧
    const accumulatedDepreciation_50 = 50;
    //固定资产减值准备
    const fixedAssetsDepreciationReserves = 51;
    //在建工程
    const constructionInProgress = 52;
    //职工薪酬
    const employeeRemuneration_54 = 54;
    //职工薪酬
    const employeeRemuneration_56 = 56;
    //职工薪酬
    const employeeRemuneration_58 = 58;
    //职工薪酬
    const employeeRemuneration_60 = 60;
    //工程物资
    const engineeringMaterials = 61;
    //固定资产清理
    const liquidationOfFixedAssets = 65;
    //生产性生物资产
    const productiveBiologicalAssets = 66;
    //生产性生物资产累计折旧
    const accumulatedDepreciationOfProductiveBiologicalAssets = 67;
    //无形资产
    const intangibleAssets = 68;
    //累计摊销
    const accumulatedAmortization = 69;
    //无形资产减值准备
    const intangibleAssetsDepreciationReserves = 70;
    //商誉
    const goodwill = 71;
    //长期待摊费用
    const longTermPendingExpenses = 72;
    //递延所得税资产
    const deferredTaxAssets = 73;
    //待处理财产损溢
    const waitDealAssetsLossOrIncome = 74;
    //待处理非流动资产损溢
    const lossAndLossOfNonCurrentAssetsToBeProcessed = 76;
    //短期借款
    const shortTermLoan = 77;
    //交易性金融负债
    const transactionalFinancialLiabilities = 78;
    //应付票据
    const notesPayable = 79;
    //应付账款
    const accountsPayable = 80;
    //预收账款
    const advanceAccountReceivable = 81;
    //应付职工薪酬
    const payableRemuneration = 82;
    //应交税费
    const taxesPayable = 92;
    //进项税额
    const amountOfTaxesOnPurchases = 94;
    //已交税金
    const payingTax = 95;
    //减免税款
    const taxDeduction = 96;
    //销项税额
    const outputTax = 99;
    //应交所得税
    const incomeTaxPayable = 107;
    //应付利息
    const interestPayable = 120;
    //应付股利
    const dividendsPayable = 121;
    //其他应付款
    const otherAccountsPayable = 122;
    //递延收益
    const deferredIncome = 125;
    //长期借款
    const longTermLoan = 126;
    //应付债券
    const bondsPayable = 127;
    //长期应付款
    const longTermAccountsPayable = 131;
    //未确认融资费用
    const unconfirmedFinancingCosts = 132;
    //专项应付款
    const specialAccountsPayable = 133;
    //预计负债
    const projectedLiabilities = 134;
    //递延所得税负债
    const deferredTaxLiability = 135;
    //衍生工具
    const derivatives = 138;
    //实收资本
    const paidInCapital = 141;
    //资本公积
    const capitalSurplus = 142;
    //其他权益工具
    const otherEquityInstruments = 147;
    //盈余公积
    const surplusReserves = 153;
    //本年利润
    const profitThisYear = 157;
    //利润分配
    const profitDistribution = 158;
    //库存股
    const treasuryStock = 166;
    //生产成本
    const productionCosts = 167;
    //职工薪酬
    const employeeRemuneration_169 = 169;
    //职工薪酬
    const employeeRemuneration_173 = 173;
    //制造费用
    const manufacturingCost = 176;
    //职工薪酬
    const employeeRemuneration_177 = 177;
    //研发支出
    const rdExpenditure = 181;
    //工程施工
    const engineeringConstruction = 184;
    //以前年度损益
    const earningsAndLossesOfPreviousYears = 187;
    //主营业务收入
    const mainBusinessIncome = 188;
    //其他业务收入
    const otherBusinessIncome = 189;
    //公允价值变动损益
    const fairValueChangeGainsAndLosses = 190;
    //投资收益
    const incomeFromInvestment = 191;
    //营业外收入
    const outOfBusinessIncome = 192;
    //非流动资产处置利得
    const proceedsFromDisposalOfNonCurrentAssets = 193;
    //政府补助
    const governmentGrants = 194;
    //捐赠收益
    const donationIncome = 195;
    //主营业务成本
    const mainBusinessCost = 198;
    //其他业务成本
    const otherBusinessCosts = 199;
    //税金及附加
    const taxesAndSurcharges = 200;
    //销售费用
    const sellingExpenses = 214;
    //职工薪酬
    const employeeRemuneration_223 = 223;
    //管理费用
    const managementCost = 230;
    //职工薪酬
    const employeeRemuneration_239 = 239;
    //财务费用
    const financialCost = 245;
    //汇兑损益
    const exchangeGainsAndLosses = 246;
    //利息
    const interest_247 = 247;
    //应收票据贴现利息
    const discountInterestOnNotesReceivable = 248;
    //资产减值损失
    const assetsImpairmentLoss = 252;
    //营业外支出
    const outOfBusinessExpenses = 253;
    //非流动资产处置净损失
    const netLossOfDisposalOfNonCurrentAssets = 255;
    //所得税费用
    const incomeTaxExpenses = 264;
    //当期所得税费用
    const currentIncomeTaxExpenses = 265;
}
