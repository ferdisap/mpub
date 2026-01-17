<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform"
  xmlns:php="http://php.net/xsl" xmlns:fo="http://www.w3.org/1999/XSL/Format">

  <!-- 
    * timeLimitInfo adalah elemen yang digunakan untuk membuat planning berdasarkan equipment/part
    * inspectionDefinition adalah element yang digunakan untuk membuat planning berdasarkan waktu inspection
    * taskDefinition digunakan berdasarkan nomor task
    * maintenanceAllocation ... TBD 
    * see S1000D_5.0 page 771/3503
   -->

  <!-- 
    Outstanding:
    1. belum memfungsikan @maintPlanningType, the textual information used to classify the maintenance planning data
    2. belum memfungsikan inspectionDefinition|timeLimitInfo|maintAllocation|toolList|remarkList
   -->

  <!-- 
    Outstanding elemen threshold:
    1. attribute @thresholdType wajib diisi untuk membedakan mana yang threshold dan interval
   -->

   <!-- 
    Outstanding element taskDefinition, baik di named or matched template:
    1. setiap element treshold dan attributenya @thresholdType, must be filled by the 'threshold' or 'interval'.
    2. belum mengakomodir attribute @worthinessLimit
    3. belum mengakomodir attribute @reduceMaint
    4. belum mengakomodir attribute @skillLevelCode
    5. belum mengakomodir attribute @skillLevelType
    6. saat ini, applicRefId, changeMark, id, controlAuthority, security dilakukan di parentnya, yaitu element taskDefinition
    7. saat ini belum difungsikan elemen equip/partRef dan equip/catalogSeqNumberRef dan equip/natoStockNumber
   -->

   <!-- 
    Outstanding element relatedTask
    1. bleum difungsikan attribute realtedTaskDesc meskipun mandatory ditulis
   -->
   <xsl:template match="maintPlanning">
    <xsl:apply-templates select="commonInfo"/>
    <xsl:apply-templates select="preliminaryRqmts"/>
    <xsl:variable name="entryType" select="php:function('Ptdi\Mpub\Transformer\Pdf::get_pmEntryType')"/>
    <xsl:choose>
      <xsl:when test="taskDefinition and $entryType = 'Structure Inspection Program'">
        <xsl:call-template name="add_taskDefinition_for_structuralInspectionProgram"/>
      </xsl:when>
      <xsl:when test="taskDefinition and $entryType = 'System And Powerplant Inspection Program'">
        <xsl:call-template name="add_taskDefinition_for_systemAndPowerPlantInspectionProgram"/>
      </xsl:when>
      <xsl:when test="taskDefinition and $entryType = 'Zonal Inspection Program'">
        <xsl:call-template name="add_taskDefinition_for_zonalInspectionProgram"/>
      </xsl:when>
      <xsl:when test="taskDefinition and $entryType = 'Time Limit Program'">
        <xsl:call-template name="add_taskDefinition_for_timeLimitProgram"/>
      </xsl:when>
      <xsl:otherwise>
        <xsl:choose>
          <!-- default table jika tidak pakai PMC -->
          <xsl:when test="taskDefinition">
            <fo:block>
              <fo:table width="100%" border="1pt solid black">
                <fo:table-column column-number="1" column-width="7%"/>
                <fo:table-column column-number="2" column-width="13%"/>
                <fo:table-column column-number="3" column-width="6%"/>
                <fo:table-column column-number="4" column-width="7%"/>
                <fo:table-column column-number="5" column-width="7%"/>
                <fo:table-column column-number="6" column-width="5%"/>
                <fo:table-column column-number="7" column-width="6%"/>
                <fo:table-column column-number="8" column-width="49%"/>
                <fo:table-header border="1pt solid black">
                  <fo:table-row>
                    <fo:table-cell border-right="1pt solid black" display-align="center" text-align="center" number-rows-spanned="2"><fo:block>Task No.</fo:block></fo:table-cell>
                    <fo:table-cell border-right="1pt solid black" display-align="center" text-align="center" number-rows-spanned="2"><fo:block>Ref</fo:block></fo:table-cell>
                    <fo:table-cell border-right="1pt solid black" display-align="center" text-align="center" number-rows-spanned="2"><fo:block>Met</fo:block></fo:table-cell>
                    <fo:table-cell border-right="1pt solid black" display-align="center" text-align="center" column-number="4" number-columns-spanned="2" border-bottom="1pt solid black">
                      <fo:block>Inspection</fo:block>
                    </fo:table-cell>
                    <fo:table-cell border-right="1pt solid black" display-align="center" text-align="center" column-number="6" number-rows-spanned="2"><fo:block>Zon</fo:block></fo:table-cell>
                    <fo:table-cell border-right="1pt solid black" display-align="center" text-align="center" number-rows-spanned="2"><fo:block>Acc</fo:block></fo:table-cell>
                    <fo:table-cell border-right="1pt solid black" display-align="center" text-align="center" number-rows-spanned="2"><fo:block>Description</fo:block></fo:table-cell>
                  </fo:table-row>
                  <fo:table-row>
                    <fo:table-cell border-right="1pt solid black" display-align="center" text-align="center"><fo:block>Thres</fo:block></fo:table-cell>
                    <fo:table-cell border-right="1pt solid black" display-align="center" text-align="center"><fo:block>Int</fo:block></fo:table-cell>
                  </fo:table-row>
                </fo:table-header>
                <fo:table-body border="1pt solid black">
                  <xsl:apply-templates select="taskDefinition"/>
                </fo:table-body>
              </fo:table>
            </fo:block>
          </xsl:when>
          <xsl:when test="inspectionDefinition">
            <!-- write the table here later -->
          </xsl:when>
          <xsl:when test="timeLimitInfo">
            <!-- write the table here later -->
          </xsl:when>
          <xsl:when test="maintAllocation">
            <!-- write the table here later -->
          </xsl:when>
        </xsl:choose>        
      </xsl:otherwise>
    </xsl:choose>
  </xsl:template>

  <xsl:template match="taskDefinition">
    <fo:table-row text-align="center">
      <fo:table-cell padding="2pt" border-right="1pt solid black">
        <fo:block>
          <xsl:value-of select="@taskIdent"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell padding="2pt" border-right="1pt solid black">
        <fo:block>
          <xsl:apply-templates select="refs"/>
        </fo:block>
      </fo:table-cell>
      <fo:table-cell padding="2pt" border-right="1pt solid black">
        <fo:block>
          <xsl:variable name="taskCode" select="string(@taskCode)"/>
          <xsl:value-of select="php:function('Ptdi\Mpub\Transformer\Transformator::interpret','taskCode', string($taskCode), 'abbreviation', 'default')" />
        </fo:block>
      </fo:table-cell>
      <fo:table-cell padding="2pt" border-right="1pt solid black">
        <fo:block>
          <xsl:variable name="thresUom" select="string(limit/threshold[@thresholdType = 'threshold']/@thresholdUnitOfMeasure)"/>
          <xsl:apply-templates select="limit/threshold[@thresholdType = 'threshold']"/>
          <xsl:value-of select="php:function('Ptdi\Mpub\Transformer\Transformator::interpret','thresholdUnitOfMeasure', string($thresUom), 'abbreviation', 'extension' ,'default')" />
        </fo:block>
      </fo:table-cell>
      <fo:table-cell padding="2pt" border-right="1pt solid black">
        <fo:block>
          <xsl:variable name="thresUom" select="string(limit/threshold[@thresholdType = 'interval']/@thresholdUnitOfMeasure)"/>
          <xsl:apply-templates select="limit/threshold[@thresholdType = 'interval']"/>
          <xsl:value-of select="php:function('Ptdi\Mpub\Transformer\Transformator::interpret','thresholdUnitOfMeasure', string($thresUom), 'abbreviation', 'extension' ,'default')" />
        </fo:block>
      </fo:table-cell>
      <fo:table-cell padding="2pt" border-right="1pt solid black">
        <xsl:if test="not(preliminaryRqmts/productionMaintData/workAreaLocationGroup/zoneRef)">
          <fo:block/>
        </xsl:if>
        <xsl:for-each select="preliminaryRqmts/productionMaintData/workAreaLocationGroup/zoneRef">
          <fo:block>
            <xsl:apply-templates select="."/>
          </fo:block>
        </xsl:for-each>
      </fo:table-cell>
      <fo:table-cell padding="2pt" border-right="1pt solid black">
      <xsl:if test="not(preliminaryRqmts/productionMaintData/workAreaLocationGroup/accessPointRef)">
        <fo:block/>
      </xsl:if>
        <xsl:for-each select="preliminaryRqmts/productionMaintData/workAreaLocationGroup/accessPointRef">
          <fo:block>
            <xsl:apply-templates select="."/>
          </fo:block>
        </xsl:for-each>
      </fo:table-cell>
      <fo:table-cell padding="2pt" text-align="left">
        <fo:block><xsl:apply-templates select="task"/></fo:block>
      </fo:table-cell>
    </fo:table-row>
  </xsl:template>

   <xsl:template match="task">
    <xsl:apply-templates select="taskMarker"/>
    <xsl:apply-templates select="taskTitle"/>
    <xsl:apply-templates select="taskDescr"/>
   </xsl:template>

   <xsl:template match="taskMarker">
    <fo:block text-decoration="underline"><xsl:value-of select="@markerType"/></fo:block>
   </xsl:template>

   <xsl:template match="taskDescr">
    <fo:block>
      <xsl:apply-templates/>
    </fo:block>
   </xsl:template>

   <xsl:template match="taskTitle">
    <fo:block font-weight="bold">
      <xsl:apply-templates select="."/>
    </fo:block>
   </xsl:template>

   <xsl:template match="relatedTask">
    <fo:block>
      <xsl:value-of select="string(@taskIdent)"/>
    </fo:block>
   </xsl:template>

</xsl:stylesheet>