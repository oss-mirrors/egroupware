<!-- $Id$ -->

	<xsl:template name="help_data">
		<xsl:apply-templates select="xhelp"/>
	</xsl:template>

	<xsl:template match="xhelp">
		<xsl:choose>
			<xsl:when test="list">
				<xsl:apply-templates select="list"/>
			</xsl:when>
			<xsl:otherwise>
				<xsl:apply-templates select="view"/>
			</xsl:otherwise>
		</xsl:choose>
	</xsl:template>

	<xsl:template match="list">
	<xsl:variable name="list_img" select="list_img"/>
		<table>
			<tr>
				<td colspan="2">
					<img src="{$list_img}"/>
				</td>
			</tr>
			<tr>
				<td valign="top" align="right">1</td>
				<td><xsl:value-of disable-output-escaping="yes" select="item_1"/></td>
			</tr>
			<tr>
				<td valign="top" align="right">2</td>
				<td><xsl:value-of disable-output-escaping="yes" select="item_2"/></td>
			</tr>
			<tr>
				<td valign="top" align="right">3</td>
				<td><xsl:value-of disable-output-escaping="yes" select="item_3"/></td>
			</tr>
			<tr>
				<td valign="top" align="right">4</td>
				<td><xsl:value-of disable-output-escaping="yes" select="item_4"/></td>
			</tr>
			<tr>
				<td valign="top" align="right">5</td>
				<td><xsl:value-of disable-output-escaping="yes" select="item_5"/></td>
			</tr>
			<tr>
				<td valign="top" align="right">6</td>
				<td><xsl:value-of disable-output-escaping="yes" select="item_6"/></td>
			</tr>
			<tr>
				<td valign="top" align="right">7</td>
				<td><xsl:value-of disable-output-escaping="yes" select="item_7"/></td>
			</tr>
			<tr>
				<td valign="top" align="right">8</td>
				<td><xsl:value-of disable-output-escaping="yes" select="item_8"/></td>
			</tr>
			<tr>
				<td valign="top" align="right">9</td>
				<td><xsl:value-of disable-output-escaping="yes" select="item_9"/></td>
			</tr>
			<tr>
				<td valign="top" align="right">10</td>
				<td><xsl:value-of disable-output-escaping="yes" select="item_10"/></td>
			</tr>
			<tr>
				<td valign="top" align="right">11</td>
				<td><xsl:value-of disable-output-escaping="yes" select="item_11"/></td>
			</tr>
			<tr>
				<td valign="top" align="right">12</td>
				<td><xsl:value-of disable-output-escaping="yes" select="item_12"/></td>
			</tr>
			<tr>
				<td valign="top" align="right">13</td>
				<td><xsl:value-of disable-output-escaping="yes" select="item_13"/></td>
			</tr>
			<tr>
				<td valign="top" align="right">14</td>
				<td><xsl:value-of disable-output-escaping="yes" select="item_14"/></td>
			</tr>
			<tr>
				<td valign="top" align="right">15</td>
				<td><xsl:value-of disable-output-escaping="yes" select="item_15"/></td>
			</tr>
			<tr>
				<td valign="top" align="right">16</td>
				<td><xsl:value-of disable-output-escaping="yes" select="item_16"/></td>
			</tr>
			<tr>
				<td valign="top" align="right">17</td>
				<td><xsl:value-of disable-output-escaping="yes" select="item_17"/></td>
			</tr>
			<tr>
				<td valign="top" align="right">18</td>
				<td><xsl:value-of disable-output-escaping="yes" select="item_18"/></td>
			</tr>
		</table>
	</xsl:template>

	<xsl:template match="view">
	<xsl:variable name="view_img" select="view_img"/>
		<table>
			<tr>
				<td colspan="2">
					<img src="{$view_img}"/>
				</td>
			</tr>
			<tr>
				<td valign="top" align="right">1</td>
				<td><xsl:value-of disable-output-escaping="yes" select="item_1"/></td>
			</tr>
			<tr>
				<td valign="top" align="right">2</td>
				<td><xsl:value-of disable-output-escaping="yes" select="item_2"/></td>
			</tr>
			<tr>
				<td valign="top" align="right">3</td>
				<td><xsl:value-of disable-output-escaping="yes" select="item_3"/></td>
			</tr>
			<tr>
				<td valign="top" align="right">4</td>
				<td><xsl:value-of disable-output-escaping="yes" select="item_4"/></td>
			</tr>
			<tr>
				<td valign="top" align="right">5</td>
				<td><xsl:value-of disable-output-escaping="yes" select="item_5"/></td>
			</tr>
			<tr>
				<td valign="top" align="right">6</td>
				<td><xsl:value-of disable-output-escaping="yes" select="item_6"/></td>
			</tr>
			<tr>
				<td valign="top" align="right">7</td>
				<td><xsl:value-of disable-output-escaping="yes" select="item_7"/></td>
			</tr>
			<tr>
				<td valign="top" align="right">8</td>
				<td><xsl:value-of disable-output-escaping="yes" select="item_8"/></td>
			</tr>
			<tr>
				<td valign="top" align="right">9</td>
				<td><xsl:value-of disable-output-escaping="yes" select="item_9"/></td>
			</tr>
		</table>
	</xsl:template>
