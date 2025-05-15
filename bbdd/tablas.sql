
--
-- Індекси таблиці `abilities`
--
ALTER TABLE `abilities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Індекси таблиці `auditoria_evoluciones`
--
ALTER TABLE `auditoria_evoluciones`
  ADD PRIMARY KEY (`id`);

--
-- Індекси таблиці `evolutions`
--
ALTER TABLE `evolutions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `evolution_chain_id` (`evolution_chain_id`),
  ADD KEY `base_pokemon_id` (`base_pokemon_id`),
  ADD KEY `evolved_pokemon_id` (`evolved_pokemon_id`);

--
-- Індекси таблиці `evolution_audit`
--
ALTER TABLE `evolution_audit`
  ADD PRIMARY KEY (`id`);

--
-- Індекси таблиці `evolution_chains`
--
ALTER TABLE `evolution_chains`
  ADD PRIMARY KEY (`id`);

--
-- Індекси таблиці `learn_methods`
--
ALTER TABLE `learn_methods`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Індекси таблиці `moves`
--
ALTER TABLE `moves`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`),
  ADD KEY `type_id` (`type_id`);

--
-- Індекси таблиці `pokemon`
--
ALTER TABLE `pokemon`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `pokedex_number` (`pokedex_number`),
  ADD KEY `idx_pokemon_name` (`name`),
  ADD KEY `idx_pokemon_pokedex_number` (`pokedex_number`),
  ADD KEY `idx_pokemon_primary_type` (`primary_type_id`),
  ADD KEY `idx_pokemon_secondary_type` (`secondary_type_id`),
  ADD KEY `idx_pokemon_legendary` (`is_legendary`),
  ADD KEY `idx_pokemon_mythical` (`is_mythical`),
  ADD KEY `idx_pokemon_generation` (`generation`);

--
-- Індекси таблиці `pokemon_abilities`
--
ALTER TABLE `pokemon_abilities`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_pokemon_ability` (`pokemon_id`,`ability_id`),
  ADD KEY `ability_id` (`ability_id`);

--
-- Індекси таблиці `pokemon_moves`
--
ALTER TABLE `pokemon_moves`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pokemon_id` (`pokemon_id`),
  ADD KEY `move_id` (`move_id`),
  ADD KEY `learn_method_id` (`learn_method_id`);

--
-- Індекси таблиці `pokemon_stats`
--
ALTER TABLE `pokemon_stats`
  ADD PRIMARY KEY (`id`),
  ADD KEY `pokemon_id` (`pokemon_id`);

--
-- Індекси таблиці `types`
--
ALTER TABLE `types`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `name` (`name`);

--
-- Індекси таблиці `type_effectiveness`
--
ALTER TABLE `type_effectiveness`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_type_matchup` (`attacking_type_id`,`defending_type_id`),
  ADD KEY `defending_type_id` (`defending_type_id`);


--
-- AUTO_INCREMENT для таблиці `abilities`
--
ALTER TABLE `abilities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=368;

--
-- AUTO_INCREMENT для таблиці `auditoria_evoluciones`
--
ALTER TABLE `auditoria_evoluciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT для таблиці `evolutions`
--
ALTER TABLE `evolutions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=485;

--
-- AUTO_INCREMENT для таблиці `evolution_audit`
--
ALTER TABLE `evolution_audit`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=485;

--
-- AUTO_INCREMENT для таблиці `evolution_chains`
--
ALTER TABLE `evolution_chains`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=542;

--
-- AUTO_INCREMENT для таблиці `learn_methods`
--
ALTER TABLE `learn_methods`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT для таблиці `moves`
--
ALTER TABLE `moves`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=920;

--
-- AUTO_INCREMENT для таблиці `pokemon`
--
ALTER TABLE `pokemon`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1303;

--
-- AUTO_INCREMENT для таблиці `pokemon_abilities`
--
ALTER TABLE `pokemon_abilities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2918;

--
-- AUTO_INCREMENT для таблиці `pokemon_moves`
--
ALTER TABLE `pokemon_moves`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=609927;

--
-- AUTO_INCREMENT для таблиці `pokemon_stats`
--
ALTER TABLE `pokemon_stats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1303;

--
-- AUTO_INCREMENT для таблиці `types`
--
ALTER TABLE `types`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT для таблиці `type_effectiveness`
--
ALTER TABLE `type_effectiveness`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=61;

--
-- Обмеження зовнішнього ключа збережених таблиць
--

--
-- Обмеження зовнішнього ключа таблиці `evolutions`
--
ALTER TABLE `evolutions`
  ADD CONSTRAINT `evolutions_ibfk_1` FOREIGN KEY (`evolution_chain_id`) REFERENCES `evolution_chains` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `evolutions_ibfk_2` FOREIGN KEY (`base_pokemon_id`) REFERENCES `pokemon` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `evolutions_ibfk_3` FOREIGN KEY (`evolved_pokemon_id`) REFERENCES `pokemon` (`id`) ON DELETE CASCADE;

--
-- Обмеження зовнішнього ключа таблиці `moves`
--
ALTER TABLE `moves`
  ADD CONSTRAINT `moves_ibfk_1` FOREIGN KEY (`type_id`) REFERENCES `types` (`id`);

--
-- Обмеження зовнішнього ключа таблиці `pokemon`
--
ALTER TABLE `pokemon`
  ADD CONSTRAINT `pokemon_ibfk_1` FOREIGN KEY (`primary_type_id`) REFERENCES `types` (`id`),
  ADD CONSTRAINT `pokemon_ibfk_2` FOREIGN KEY (`secondary_type_id`) REFERENCES `types` (`id`);

--
-- Обмеження зовнішнього ключа таблиці `pokemon_abilities`
--
ALTER TABLE `pokemon_abilities`
  ADD CONSTRAINT `pokemon_abilities_ibfk_1` FOREIGN KEY (`pokemon_id`) REFERENCES `pokemon` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pokemon_abilities_ibfk_2` FOREIGN KEY (`ability_id`) REFERENCES `abilities` (`id`) ON DELETE CASCADE;

--
-- Обмеження зовнішнього ключа таблиці `pokemon_moves`
--
ALTER TABLE `pokemon_moves`
  ADD CONSTRAINT `pokemon_moves_ibfk_1` FOREIGN KEY (`pokemon_id`) REFERENCES `pokemon` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pokemon_moves_ibfk_2` FOREIGN KEY (`move_id`) REFERENCES `moves` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pokemon_moves_ibfk_3` FOREIGN KEY (`learn_method_id`) REFERENCES `learn_methods` (`id`) ON DELETE CASCADE;

--
-- Обмеження зовнішнього ключа таблиці `pokemon_stats`
--
ALTER TABLE `pokemon_stats`
  ADD CONSTRAINT `pokemon_stats_ibfk_1` FOREIGN KEY (`pokemon_id`) REFERENCES `pokemon` (`id`) ON DELETE CASCADE;
